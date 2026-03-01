<?php
declare(strict_types=1);

namespace Fyre\Security;

use BadMethodCallException;
use Closure;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\Cookie;
use Fyre\Security\Exceptions\CsrfTokenException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function array_replace_recursive;
use function assert;
use function base64_decode;
use function base64_encode;
use function chr;
use function hash_equals;
use function hash_hmac;
use function in_array;
use function is_array;
use function ord;
use function random_bytes;
use function strlen;
use function substr;
use function time;

/**
 * Generates and validates CSRF tokens for requests.
 */
class CsrfProtection
{
    use DebugTrait;

    protected const CHECK_METHODS = [
        'DELETE',
        'PATCH',
        'POST',
        'PUT',
    ];

    protected const TOKEN_LENGTH = 16;

    /**
     * @var array<string, mixed>
     */
    protected static array $defaults = [
        'cookie' => [
            'name' => 'CsrfToken',
            'expires' => 0,
            'domain' => '',
            'path' => '/',
            'secure' => true,
            'httpOnly' => false,
            'sameSite' => 'Lax',
        ],
        'field' => 'csrf_token',
        'header' => 'Csrf-Token',
        'salt' => null,
        'skipCheck' => null,
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $cookieOptions;

    protected string|null $field;

    protected string|null $header;

    protected string $salt;

    protected Closure|null $skipCheck;

    protected string|null $token;

    /**
     * Constructs a CsrfProtection.
     *
     * @param Container $container The Container.
     * @param Config $config The Config.
     */
    public function __construct(
        protected Container $container,
        Config $config
    ) {
        $options = array_replace_recursive(static::$defaults, $config->get('Csrf', []));

        $this->cookieOptions = $options['cookie'];
        $this->field = $options['field'];
        $this->header = $options['header'];
        $this->salt = $options['salt'];
        $this->skipCheck = $options['skipCheck'];
    }

    /**
     * Updates the Response before sending to the client.
     *
     * Note: This will add the CSRF cookie if it is missing from the request.
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @param ResponseInterface $response The Response.
     * @return ResponseInterface The Response instance.
     */
    public function beforeResponse(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $cookieName = $this->cookieOptions['name'];

        if (isset($request->getCookieParams()[$cookieName])) {
            return $response;
        }

        $cookieOptions = $this->cookieOptions;

        if ($cookieOptions['expires']) {
            $cookieOptions['expires'] += time();
        }

        $cookie = new Cookie($cookieName, $this->getCookieToken(), $cookieOptions);

        return $response->withAddedHeader('Set-Cookie', (string) $cookie);
    }

    /**
     * Checks the CSRF token.
     *
     * Note: This attaches the current {@see CsrfProtection} instance to the request as the
     * `csrf` attribute, and may remove the token field from the parsed body.
     * Only state-changing HTTP methods are checked by default.
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @return ServerRequestInterface The ServerRequest instance.
     *
     * @throws BadMethodCallException If CSRF protection has already been enabled.
     * @throws CsrfTokenException If the token is invalid.
     */
    public function checkToken(ServerRequestInterface $request): ServerRequestInterface
    {
        if ($request->getAttribute('csrf')) {
            throw new BadMethodCallException('CSRF protection has already been enabled.');
        }

        $request = $request->withAttribute('csrf', $this);

        $hasData = in_array($request->getMethod(), static::CHECK_METHODS, true);
        $userToken = null;

        if ($hasData && $this->field) {
            $data = $request->getParsedBody();

            if (is_array($data) && isset($data[$this->field])) {
                $userToken = $data[$this->field];

                unset($data[$this->field]);

                $request = $request->withParsedBody($data);
            }
        }

        $cookieName = $this->cookieOptions['name'];

        $this->token = $request->getCookieParams()[$cookieName] ?? null;

        if (!$hasData || ($this->skipCheck && $this->container->call($this->skipCheck, ['request' => $request]) === true)) {
            return $request;
        }

        if ($this->header) {
            $userToken ??= $request->getHeaderLine($this->header);
        }

        if (
            !$userToken ||
            !$this->token ||
            !$this->verifyToken($this->token) ||
            !hash_equals((string) $this->unsaltToken($userToken), $this->token)
        ) {
            throw new CsrfTokenException();
        }

        return $request;
    }

    /**
     * Returns the CSRF cookie token.
     *
     * @return string The CSRF cookie token.
     */
    public function getCookieToken(): string
    {
        return $this->token ??= $this->createToken();
    }

    /**
     * Returns the CSRF token field name.
     *
     * @return string|null The CSRF token field name.
     */
    public function getField(): string|null
    {
        return $this->field;
    }

    /**
     * Returns the CSRF form token.
     *
     * Note: This returns a salted token suitable for embedding in HTML forms.
     *
     * @return string|null The CSRF form token.
     */
    public function getFormToken(): string|null
    {
        return $this->saltToken($this->getCookieToken());
    }

    /**
     * Returns the CSRF token header name.
     *
     * @return string|null The CSRF token header name.
     */
    public function getHeader(): string|null
    {
        return $this->header;
    }

    /**
     * Creates a token.
     *
     * @return string The token.
     */
    protected function createToken(): string
    {
        $token = random_bytes(static::TOKEN_LENGTH);
        $token .= hash_hmac('sha1', $token, $this->salt);

        return base64_encode($token);
    }

    /**
     * Adds salt to a token.
     *
     * @param string $token The unsalted token.
     * @return string|null The salted token, or null if the token is invalid.
     *
     * @throws InvalidArgumentException If salting fails.
     */
    protected function saltToken(string $token): string|null
    {
        $decoded = base64_decode($token, true);

        if ($decoded === false) {
            return null;
        }

        $length = strlen($decoded);

        assert($length > 0);

        $salt = random_bytes($length);
        $salted = '';
        for ($i = 0; $i < $length; $i++) {
            // XOR the token and salt together so that we can reverse it later.
            $codepoint = ord($decoded[$i]) ^ ord($salt[$i]);

            if ($codepoint < 0 || $codepoint > 255) {
                throw new InvalidArgumentException('Salting failed.');
            }

            $salted .= chr($codepoint);
        }

        return base64_encode($salted.$salt);
    }

    /**
     * Removes salt from a token.
     *
     * @param string $token The salted token.
     * @return string|null The unsalted token, or null if the token is invalid.
     *
     * @throws InvalidArgumentException If unsalting fails.
     */
    protected function unsaltToken(string $token): string|null
    {
        $decoded = base64_decode($token, true);

        if ($decoded === false) {
            return null;
        }

        $length = static::TOKEN_LENGTH + 40;
        $salted = substr($decoded, 0, $length);
        $salt = substr($decoded, $length);

        $unsalted = '';
        for ($i = 0; $i < $length; $i++) {
            // Reverse the XOR to desalt.
            $codepoint = ord($salted[$i]) ^ ord($salt[$i]);

            if ($codepoint < 0 || $codepoint > 255) {
                throw new InvalidArgumentException('Unsalting failed.');
            }

            $unsalted .= chr($codepoint);
        }

        return base64_encode($unsalted);
    }

    /**
     * Checks whether a token is valid.
     *
     * @param string $token The token.
     * @return bool Whether the token is valid.
     */
    protected function verifyToken(string $token): bool
    {
        $decoded = base64_decode($token, true);

        if ($decoded === false) {
            return false;
        }

        $length = strlen($decoded);

        if ($length <= static::TOKEN_LENGTH) {
            return false;
        }

        $key = substr($decoded, 0, static::TOKEN_LENGTH);
        $hmac = substr($decoded, static::TOKEN_LENGTH);

        $expectedHmac = hash_hmac('sha1', $key, $this->salt);

        return hash_equals($hmac, $expectedHmac);
    }
}
