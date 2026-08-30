<?php
declare(strict_types=1);

namespace Fyre\Http;

use Fyre\Core\Config;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\TypeParser;
use Fyre\Http\Exceptions\BadRequestException;
use Fyre\Http\Factories\ServerRequestFactory;
use Fyre\Utility\Arr;
use InvalidArgumentException;
use Override;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

use function array_key_exists;
use function array_key_last;
use function array_reverse;
use function explode;
use function filter_var;
use function getenv;
use function in_array;
use function is_array;
use function json_decode;
use function json_last_error;
use function locale_get_default;
use function ltrim;
use function parse_str;
use function sprintf;
use function str_starts_with;
use function strtolower;
use function trim;

use const FILTER_VALIDATE_IP;
use const JSON_ERROR_NONE;
use const PHP_SAPI;

/**
 * Provides a PSR-7 {@see ServerRequestInterface} implementation.
 *
 * This class does not read PHP superglobals or derive request values from server
 * parameters. Use {@see ServerRequestFactory::createFromGlobals()} to create the current
 * SAPI request, or {@see ServerRequestFactory::createFromOptions()} to marshal raw server
 * parameters and PHP file upload data.
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    use MacroTrait;

    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * @var array<string, mixed>
     */
    protected array $cookies = [];

    /**
     * @var array<mixed>|null
     */
    protected array|null $data = null;

    protected string $defaultLocale;

    /**
     * @var array<string, mixed>
     */
    protected array $files = [];

    /**
     * @var array<string, mixed>
     */
    protected array $get = [];

    protected string|null $locale = null;

    /**
     * @var array<string, mixed>
     */
    protected array $server = [];

    /**
     * @var string[]
     */
    protected array $supportedLocales = [];

    /**
     * @var string[]
     */
    protected array $trustedProxies = [];

    protected bool $trustProxy = false;

    protected UserAgent $userAgent;

    /**
     * Constructs a ServerRequest.
     *
     * @param Config $config The Config.
     * @param TypeParser $typeParser The TypeParser.
     * @param array<string, mixed> $options The normalized request options.
     */
    public function __construct(
        Config $config,
        protected TypeParser $typeParser,
        array $options = []
    ) {
        $this->defaultLocale = $config->get('App.defaultLocale') ?? locale_get_default();
        $this->supportedLocales = $config->get('App.supportedLocales', []);
        $this->trustProxy = $config->get('App.trustProxy', false);
        $this->trustedProxies = $config->get('App.trustedProxies', []);

        $this->server = $options['server'] ?? [];
        $this->cookies = $options['cookies'] ?? [];
        $this->data = $options['data'] ?? null;
        $this->get = $options['get'] ?? [];
        $this->files = $options['files'] ?? [];

        static::validateFiles($this->files);

        parent::__construct($options['uri'] ?? null, $options);

        $userAgent = $this->getHeaderLine('User-Agent');

        $this->userAgent = new UserAgent($userAgent);

        if ($this->supportedLocales !== [] && $this->hasHeader('Accept-Language')) {
            $this->locale = $this->negotiate('language', $this->supportedLocales);
        }
    }

    /**
     * Returns an attribute from the request.
     *
     * @param string $key The key.
     * @param mixed $default The default value.
     * @return mixed The attribute value.
     */
    #[Override]
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->attributes) ?
            $this->attributes[$key] :
            $default;
    }

    /**
     * Returns all attributes from the request.
     *
     * @return array<string, mixed> The attributes.
     */
    #[Override]
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Returns the client IP address.
     *
     * Note: Uses `REMOTE_ADDR` by default. When proxy trust is enabled, `X-Forwarded-For`
     * is resolved from right to left using the configured trusted proxy list.
     *
     * @return string The client IP address.
     */
    public function getClientIp(): string
    {
        $remoteAddr = $this->getServer('REMOTE_ADDR') ?? '';

        if (
            !$this->trustProxy ||
            (
                $this->trustedProxies !== [] &&
                !in_array($remoteAddr, $this->trustedProxies, true)
            )
        ) {
            return $remoteAddr;
        }

        $forwardedFor = $this->getHeaderLine('X-Forwarded-For');

        if (!$forwardedFor) {
            return $remoteAddr;
        }

        $clientIp = $remoteAddr;
        $forwardedIps = explode(',', $forwardedFor)
            |> array_reverse(...);

        foreach ($forwardedIps as $forwardedIp) {
            $forwardedIp = trim($forwardedIp);

            if (!filter_var($forwardedIp, FILTER_VALIDATE_IP)) {
                return $clientIp;
            }

            $clientIp = $forwardedIp;

            if (!in_array($clientIp, $this->trustedProxies, true)) {
                break;
            }
        }

        return $clientIp;
    }

    /**
     * Returns a cookie parameter using "dot" notation.
     *
     * @param string|null $key The key.
     * @param string|null $as The type.
     * @return mixed The cookie value.
     */
    public function getCookie(string|null $key = null, string|null $as = null): mixed
    {
        if ($key === null) {
            return $this->getCookieParams();
        }

        $value = Arr::getDot($this->getCookieParams(), $key);

        if ($as === null) {
            return $value;
        }

        return $this->typeParser->use($as)->parse($value);
    }

    /**
     * Returns the cookie parameters.
     *
     * @return array<string, mixed> The cookie parameters.
     */
    #[Override]
    public function getCookieParams(): array
    {
        return $this->cookies;
    }

    /**
     * Returns a value from the parsed body data using "dot" notation.
     *
     * This reads from {@see ServerRequest::getParsedBody()} which may parse `php://input`
     * for certain content types/methods.
     *
     * @param string|null $key The key.
     * @param string|null $as The type.
     * @return mixed The parsed body value.
     */
    public function getData(string|null $key = null, string|null $as = null): mixed
    {
        if ($key === null) {
            return $this->getParsedBody();
        }

        $value = Arr::getDot($this->getParsedBody(), $key);

        if ($as === null) {
            return $value;
        }

        return $this->typeParser->use($as)->parse($value);
    }

    /**
     * Returns the default locale.
     *
     * @return string The default locale.
     */
    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }

    /**
     * Returns an environment variable.
     *
     * Reads values using `getenv()` rather than `$_ENV`.
     *
     * @param string $key The key.
     * @param string|null $as The type.
     * @return mixed The environment variable value.
     */
    public function getEnv(string $key, string|null $as = null): mixed
    {
        $value = getenv($key, false);

        if ($value === false) {
            return null;
        }

        if ($as === null) {
            return $value;
        }

        return $this->typeParser->use($as)->parse($value);
    }

    /**
     * Returns the current locale.
     *
     * @return string The current locale.
     */
    public function getLocale(): string
    {
        return $this->locale ?? $this->defaultLocale;
    }

    /**
     * Returns the parsed body data.
     *
     * - For `application/x-www-form-urlencoded` with `PUT`, `PATCH`, or `DELETE`, the body is
     *   parsed using `parse_str()`.
     * - For `application/json`, the body is JSON-decoded into an associative array.
     * - Otherwise, an empty array is used.
     *
     * @return array<mixed> The parsed body data.
     *
     * @throws BadRequestException If the request is invalid.
     */
    #[Override]
    public function getParsedBody(): array
    {
        if ($this->data === null) {
            $contentType = $this->getHeaderLine('Content-Type');

            if (str_starts_with($contentType, 'application/x-www-form-urlencoded') && in_array($this->method, ['PUT', 'PATCH', 'DELETE'], true)) {
                parse_str((string) $this->body, $this->data);
            } else if (str_starts_with($contentType, 'application/json')) {
                $data = json_decode((string) $this->body, true);

                if (
                    json_last_error() !== JSON_ERROR_NONE ||
                    !is_array($data)
                ) {
                    throw new BadRequestException('The request body is not valid.');
                }

                $this->data = $data;
            } else {
                $this->data = [];
            }
        }

        return $this->data;
    }

    /**
     * Returns a query parameter using "dot" notation.
     *
     * @param string|null $key The key.
     * @param string|null $as The type.
     * @return mixed The query value.
     */
    public function getQuery(string|null $key = null, string|null $as = null): mixed
    {
        if ($key === null) {
            return $this->getQueryParams();
        }

        $value = Arr::getDot($this->getQueryParams(), $key);

        if ($as === null) {
            return $value;
        }

        return $this->typeParser->use($as)->parse($value);
    }

    /**
     * Returns the query parameters.
     *
     * @return array<string, mixed> The query parameters.
     */
    #[Override]
    public function getQueryParams(): array
    {
        return $this->get;
    }

    /**
     * Returns a server parameter using "dot" notation.
     *
     * @param string|null $key The key.
     * @param string|null $as The type.
     * @return mixed The server value.
     */
    public function getServer(string|null $key = null, string|null $as = null): mixed
    {
        if ($key === null) {
            return $this->getServerParams();
        }

        $value = Arr::getDot($this->getServerParams(), $key);

        if ($as === null) {
            return $value;
        }

        return $this->typeParser->use($as)->parse($value);
    }

    /**
     * Returns the server parameters.
     *
     * @return array<string, mixed> The server parameters.
     */
    #[Override]
    public function getServerParams(): array
    {
        return $this->server;
    }

    /**
     * Returns the trusted proxy IPs.
     *
     * @return string[] The trusted proxy IPs.
     */
    public function getTrustedProxies(): array
    {
        return $this->trustedProxies;
    }

    /**
     * Returns an UploadedFile or nested file array using "dot" notation.
     *
     * @param string|null $key The key.
     * @return mixed The uploaded file value.
     */
    public function getUploadedFile(string|null $key = null): mixed
    {
        if ($key === null) {
            return $this->getUploadedFiles();
        }

        $files = $this->getUploadedFiles();

        return Arr::getDot($files, $key);
    }

    /**
     * Returns the uploaded files.
     *
     * @return array<string, mixed> The uploaded files.
     */
    #[Override]
    public function getUploadedFiles(): array
    {
        return $this->files;
    }

    /**
     * Returns the user agent.
     *
     * @return UserAgent The UserAgent instance.
     */
    public function getUserAgent(): UserAgent
    {
        return $this->userAgent;
    }

    /**
     * Checks whether the request was made using AJAX.
     *
     * @return bool Whether the request was made using AJAX.
     */
    public function isAjax(): bool
    {
        $xRequestedWith = $this->getHeaderLine('X-Requested-With');

        return $xRequestedWith && strtolower($xRequestedWith) === 'xmlhttprequest';
    }

    /**
     * Checks whether the request was made from the CLI.
     *
     * @return bool Whether the request was made from the CLI.
     */
    public function isCli(): bool
    {
        return PHP_SAPI === 'cli';
    }

    /**
     * Checks whether the request is using HTTPS.
     *
     * Checks the `HTTPS` server param and trusted proxy headers (`X-Forwarded-Proto` and
     * `Front-End-Https`).
     *
     * @return bool Whether the request is using HTTPS.
     */
    public function isSecure(): bool
    {
        $https = $this->getServer('HTTPS');

        if ($https && strtolower($https) !== 'off') {
            return true;
        }

        $remoteAddr = $this->getServer('REMOTE_ADDR') ?? '';

        if (
            !$this->trustProxy ||
            (
                $this->trustedProxies !== [] &&
                !in_array($remoteAddr, $this->trustedProxies, true)
            )
        ) {
            return false;
        }

        $xForwardedProto = $this->getHeaderLine('X-Forwarded-Proto');

        if ($xForwardedProto) {
            $forwardedProtocols = explode(',', $xForwardedProto);
            $forwardedProtocol = $forwardedProtocols[array_key_last($forwardedProtocols)]
                |> trim(...)
                |> strtolower(...);

            if ($forwardedProtocol === 'https') {
                return true;
            }
        }

        $frontEndHttps = $this->getHeaderLine('Front-End-Https');

        return $frontEndHttps && strtolower($frontEndHttps) !== 'off';
    }

    /**
     * Negotiates a value from HTTP headers.
     *
     * @param 'content'|'encoding'|'language' $type The negotiation type.
     * @param string[] $supported The supported values.
     * @param bool $strictMatch Whether to not use a default fallback.
     * @return string The negotiated value.
     *
     * @throws InvalidArgumentException If the negotiation type is not valid.
     */
    public function negotiate(string $type, array $supported, bool $strictMatch = false): string
    {
        switch ($type) {
            case 'content':
                $accepted = $this->getHeaderLine('Accept');

                return Negotiate::content($accepted, $supported, $strictMatch);
            case 'encoding':
                $accepted = $this->getHeaderLine('Accept-Encoding');

                return Negotiate::encoding($accepted, $supported);
            case 'language':
                $accepted = $this->getHeaderLine('Accept-Language');

                return Negotiate::language($accepted, $supported);
            default:
                throw new InvalidArgumentException(sprintf(
                    'Negotiation type `%s` is not valid.',
                    $type
                ));
        }
    }

    /**
     * Checks whether the request prefers JSON responses.
     *
     * @return bool Whether JSON is the preferred content type.
     */
    public function prefersJson(): bool
    {
        return $this->negotiate(
            'content',
            ['application/json', 'text/html'],
            true
        ) === 'application/json';
    }

    /**
     * Returns the new ServerRequest instance with the updated attribute.
     *
     * @param string $key The attribute key.
     * @param mixed $value The attribute value.
     * @return static The new ServerRequest instance with the updated attribute.
     */
    #[Override]
    public function withAttribute(string $key, mixed $value): static
    {
        $temp = clone $this;

        $temp->attributes[$key] = $value;

        return $temp;
    }

    /**
     * Returns the new ServerRequest instance with updated cookie parameters.
     *
     * @param array<string, mixed> $data The cookie parameters.
     * @return static The new ServerRequest instance with the updated cookie parameters.
     */
    #[Override]
    public function withCookieParams(array $data): static
    {
        $temp = clone $this;

        $temp->cookies = $data;

        return $temp;
    }

    /**
     * Returns the new ServerRequest instance with the updated locale.
     *
     * @param string $locale The locale.
     * @return static The new ServerRequest instance with the updated locale.
     *
     * @throws InvalidArgumentException If the locale is not supported.
     */
    public function withLocale(string $locale): static
    {
        if (!in_array($locale, $this->supportedLocales, true)) {
            throw new InvalidArgumentException(sprintf(
                'Locale `%s` is not supported.',
                $locale
            ));
        }

        $temp = clone $this;

        $temp->locale = $locale;

        return $temp;
    }

    /**
     * Returns the new ServerRequest instance without the attribute.
     *
     * @param string $key The attribute key.
     * @return static The new ServerRequest instance without the attribute.
     */
    #[Override]
    public function withoutAttribute(string $key): static
    {
        $temp = clone $this;

        unset($temp->attributes[$key]);

        return $temp;
    }

    /**
     * Returns the new ServerRequest instance with updated parsed body data.
     *
     * @param array<string, mixed>|null $data The parsed body data.
     * @return static The new ServerRequest instance with the updated parsed body data.
     *
     * @throws InvalidArgumentException If the parsed body is not an array or null.
     */
    #[Override]
    public function withParsedBody(mixed $data): static
    {
        if ($data !== null && !is_array($data)) {
            throw new InvalidArgumentException('Parsed body data must be an array or null.');
        }

        $temp = clone $this;

        $temp->data = $data;

        return $temp;
    }

    /**
     * Returns the new ServerRequest instance with updated query parameters.
     *
     * @param array<string, mixed> $data The query parameters.
     * @return static The new ServerRequest instance with the updated query parameters.
     */
    #[Override]
    public function withQueryParams(array $data): static
    {
        $temp = clone $this;

        $temp->get = $data;

        return $temp;
    }

    /**
     * Returns the new ServerRequest instance with updated server parameters.
     *
     * @param array<string, mixed> $data The server parameters.
     * @return static The new ServerRequest instance with the updated server parameters.
     */
    public function withServerParams(array $data): static
    {
        $temp = clone $this;

        $temp->server = $data;

        return $temp;
    }

    /**
     * Returns the new ServerRequest instance with updated uploaded files.
     *
     * Note: This implementation expects {@see UploadedFileInterface} instances (and nested
     * arrays of them) and will throw if other values are provided.
     *
     * @param array<string, mixed> $data The uploaded files.
     * @return static The new ServerRequest instance with the updated uploaded files.
     */
    #[Override]
    public function withUploadedFiles(array $data): static
    {
        static::validateFiles($data);

        $temp = clone $this;

        $temp->files = $data;

        return $temp;
    }

    /**
     * Validates uploaded files.
     *
     * @param array<string, mixed> $files The files to validate.
     * @param string $path The file path.
     *
     * @throws InvalidArgumentException If an invalid uploaded file is found.
     */
    protected static function validateFiles(array $files, string $path = ''): void
    {
        foreach ($files as $key => $file) {
            $filePath = ltrim($path.'.'.$key, '.');

            if (is_array($file)) {
                static::validateFiles($file, $filePath);

                continue;
            }

            if ($file instanceof UploadedFileInterface) {
                continue;
            }

            throw new InvalidArgumentException(sprintf(
                'Uploaded file `%s` is not valid.',
                $filePath
            ));
        }
    }
}
