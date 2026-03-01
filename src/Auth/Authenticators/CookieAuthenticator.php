<?php
declare(strict_types=1);

namespace Fyre\Auth\Authenticators;

use Fyre\Auth\Authenticator;
use Fyre\Core\Attributes\SensitivePropertyArray;
use Fyre\Http\Cookie;
use Fyre\ORM\Entity;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function count;
use function hash_hmac;
use function json_decode;
use function json_encode;
use function password_hash;
use function password_verify;
use function time;

use const PASSWORD_DEFAULT;

/**
 * Authenticator that persists identity via cookies.
 */
class CookieAuthenticator extends Authenticator
{
    /**
     * @var array<string, mixed>
     */
    #[Override]
    protected static array $defaults = [
        'cookieName' => 'auth',
        'cookieOptions' => [
            'httpOnly' => true,
        ],
        'identifierField' => 'email',
        'passwordField' => 'password',
        'salt' => null,
    ];

    /**
     * @var array<string, mixed>
     */
    #[Override]
    #[SensitivePropertyArray(['salt'])]
    protected array $config;

    protected bool|null $sendCookie = null;

    /**
     * {@inheritDoc}
     *
     * Note: When the cookie is invalid, this authenticator marks it for deletion on the next response.
     */
    #[Override]
    public function authenticate(ServerRequestInterface $request): Entity|null
    {
        $cookieName = $this->config['cookieName'];
        $cookie = $request->getCookieParams()[$cookieName] ?? null;

        if (!$cookie) {
            return null;
        }

        $data = json_decode($cookie, true);

        if (!$data || count($data) !== 2) {
            $this->logout();

            return null;
        }

        [$identifier, $tokenHash] = $data;

        $user = $this->auth->identifier()->identify($identifier);

        if (!$user) {
            $this->logout();

            return null;
        }

        $token = $this->createToken($user);

        if (!password_verify($token, $tokenHash)) {
            $this->logout();

            return null;
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     *
     * Writes or clears the cookie depending on whether the user is being remembered.
     */
    #[Override]
    public function beforeResponse(ResponseInterface $response, Entity|null $user = null): ResponseInterface
    {
        if ($this->sendCookie === false) {
            $cookieOptions = $this->config['cookieOptions'];
            $cookieOptions['expires'] = 1;

            return $response->withAddedHeader(
                'Set-Cookie',
                new Cookie($this->config['cookieName'], '', $cookieOptions)->toHeaderString()
            );
        }

        if ($user && $this->sendCookie === true) {
            $identifier = $user->get($this->config['identifierField']);

            $token = $this->createToken($user);
            $tokenHash = password_hash($token, PASSWORD_DEFAULT);

            $value = (string) json_encode([$identifier, $tokenHash]);

            $cookieOptions = $this->config['cookieOptions'];

            if (isset($cookieOptions['expires'])) {
                $cookieOptions['expires'] += time();
            }

            return $response->withAddedHeader(
                'Set-Cookie',
                new Cookie($this->config['cookieName'], $value, $cookieOptions)->toHeaderString()
            );
        }

        return $response;
    }

    /**
     * {@inheritDoc}
     *
     * Note: The cookie is only written when `$rememberMe` is true.
     */
    #[Override]
    public function login(Entity $user, bool $rememberMe = false): void
    {
        if ($rememberMe) {
            $this->sendCookie = true;
        }
    }

    /**
     * {@inheritDoc}
     *
     * Marks the cookie for deletion on the next response.
     */
    #[Override]
    public function logout(): void
    {
        $this->sendCookie = false;
    }

    /**
     * Creates a token for a user.
     *
     * Note: The token is derived from the configured identifier and password fields so it is invalidated
     * automatically when the stored password changes. When a salt is configured, an HMAC is appended.
     *
     * @param Entity $user The Entity.
     * @return string The token.
     */
    protected function createToken(Entity $user): string
    {
        $identifier = $user->get($this->config['identifierField']);
        $password = $user->get($this->config['passwordField']);

        $value = $identifier.$password;

        if (!$this->config['salt']) {
            return $value;
        }

        $hash = hash_hmac('sha1', $value, $this->config['salt']);

        return $value.$hash;
    }
}
