<?php
declare(strict_types=1);

namespace Fyre\Http;

use Fyre\Core\Config;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\TypeParser;
use Fyre\Utility\Arr;
use InvalidArgumentException;
use Override;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

use function array_key_exists;
use function array_map;
use function array_merge;
use function explode;
use function getenv;
use function in_array;
use function is_array;
use function json_decode;
use function json_last_error;
use function locale_get_default;
use function parse_str;
use function parse_url;
use function preg_match;
use function sprintf;
use function str_replace;
use function str_starts_with;
use function strtolower;
use function substr;
use function ucwords;

use const JSON_ERROR_NONE;
use const PHP_SAPI;
use const PHP_URL_PATH;

/**
 * Provides a PSR-7 {@see ServerRequestInterface} implementation backed by PHP superglobals.
 * When values are not provided via constructor options, this request lazily reads from
 * `$_SERVER`, `$_GET`, `$_POST`, `$_COOKIE`, and `$_FILES`.
 *
 * The constructor also derives the request URI (scheme/host/port/path/query) from server
 * parameters and headers and sets the default body stream to `php://input`.
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    use MacroTrait;

    /**
     * @var array<string, mixed>
     */
    protected array $attributes = [];

    /**
     * @var array<string, mixed>|null
     */
    protected array|null $cookies = null;

    /**
     * @var array<mixed>|null
     */
    protected array|null $data = null;

    protected string $defaultLocale;

    /**
     * @var array<string, mixed>|null
     */
    protected array|null $files = null;

    /**
     * @var array<string, mixed>|null
     */
    protected array|null $get = null;

    protected string|null $locale = null;

    /**
     * @var array<string, mixed>|null
     */
    protected array|null $server = null;

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
     * @param array<string, mixed> $options The request options.
     */
    public function __construct(
        Config $config,
        protected TypeParser $typeParser,
        array $options = []
    ) {
        $this->defaultLocale = $config->get('App.defaultLocale') ?? locale_get_default();
        $this->supportedLocales = $config->get('App.supportedLocales', []);

        $this->server = $options['server'] ?? null;
        $this->cookies = $options['cookies'] ?? null;
        $this->data = $options['data'] ?? null;
        $this->get = $options['get'] ?? null;
        $this->files = $options['files'] ?? null;

        if ($this->files) {
            $this->files = static::buildFiles(static::normalizeFiles($this->files));
        }

        $options['method'] ??= $this->getServer('REQUEST_METHOD');
        $options['headers'] = array_merge(static::buildHeaders($this->getServerParams()), $options['headers'] ?? []);
        $options['body'] ??= Stream::createFromFile('php://input');

        parent::__construct(null, $options);

        $scheme = $this->isSecure() ?
            'https' :
            'http';
        $host = $this->getHeaderLine('Host');
        $port = null;

        if ($host && preg_match('/^(.*)\:(\d+)$/', $host, $match)) {
            $host = $match[1];
            $port = (int) $match[2];
        } else if (!$host) {
            $host = $this->getServer('SERVER_NAME') ?? '';
            $port = $this->getServer('SERVER_PORT');

            if ($port) {
                $port = (int) $port;
            } else {
                $port = null;
            }
        }

        if ($host) {
            $this->uri = $this->uri
                ->withScheme($scheme)
                ->withHost($host)
                ->withPort($port);
        }

        $requestUri = $this->getServer('REQUEST_URI');

        if ($requestUri) {
            $path = (string) parse_url($requestUri, PHP_URL_PATH);
            $this->uri = $this->uri->withPath($path);
        }

        $query = $this->getServer('QUERY_STRING');

        if ($query) {
            $this->uri = $this->uri->withQuery($query);
        }

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
     * Note: Uses `REMOTE_ADDR` by default. When proxy trust is enabled, the first value from
     * `X-Forwarded-For` is used only when the immediate remote address is trusted.
     *
     * @return string The client IP address.
     */
    public function getClientIp(): string
    {
        $remoteAddr = $this->getServer('REMOTE_ADDR') ?? '';

        if (!$this->trustProxy) {
            return $remoteAddr;
        }

        if (
            $this->trustedProxies !== [] &&
            !in_array($remoteAddr, $this->trustedProxies, true)
        ) {
            return $remoteAddr;
        }

        $forwardedFor = $this->getHeaderLine('X-Forwarded-For');

        if (!$forwardedFor) {
            return $remoteAddr;
        }

        return explode(',', $forwardedFor)[0] |> trim(...) ?: $remoteAddr;
    }

    /**
     * Returns a value from the $_COOKIE array using "dot" notation.
     *
     * @param string|null $key The key.
     * @param string|null $as The type.
     * @return mixed The $_COOKIE value.
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
     * Returns the $_COOKIE array.
     *
     * @return array<string, mixed> The $_COOKIE array.
     */
    #[Override]
    public function getCookieParams(): array
    {
        return $this->cookies ??= $_COOKIE;
    }

    /**
     * Returns a value from the $_POST array or parsed body data using "dot" notation.
     *
     * This reads from {@see ServerRequest::getParsedBody()} which may parse `php://input`
     * for certain content types/methods.
     *
     * @param string|null $key The key.
     * @param string|null $as The type.
     * @return mixed The $_POST or parsed body value.
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
     * - Otherwise, `$_POST` is used.
     *
     * @return array<mixed> The parsed body data.
     *
     * @throws RuntimeException If the request is invalid.
     */
    #[Override]
    public function getParsedBody(): array
    {
        if ($this->data === null) {
            $contentType = $this->getHeaderLine('Content-Type');

            if (str_starts_with($contentType, 'application/x-www-form-urlencoded') && in_array($this->method, ['PUT', 'PATCH', 'DELETE'], true)) {
                parse_str((string) $this->body, $this->data);
            } else if (str_starts_with($contentType, 'application/json')) {
                $this->data = json_decode((string) $this->body, true) ?? [];

                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new RuntimeException('The request body is not valid.');
                }
            } else {
                $this->data = $_POST;
            }
        }

        return $this->data;
    }

    /**
     * Returns a value from the $_GET array using "dot" notation.
     *
     * @param string|null $key The key.
     * @param string|null $as The type.
     * @return mixed The $_GET value.
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
     * Returns the $_GET array.
     *
     * @return array<string, mixed> The $_GET array.
     */
    #[Override]
    public function getQueryParams(): array
    {
        return $this->get ??= $_GET;
    }

    /**
     * Returns a value from the $_SERVER array using "dot" notation.
     *
     * @param string|null $key The key.
     * @param string|null $as The type.
     * @return mixed The $_SERVER value.
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
     * Returns the $_SERVER array.
     *
     * @return array<string, mixed> The $_SERVER array.
     */
    #[Override]
    public function getServerParams(): array
    {
        return $this->server ??= $_SERVER;
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
     * Returns an UploadedFile or array of files from the `$_FILES` array using "dot" notation.
     *
     * @param string|null $key The key.
     * @return mixed The `$_FILES` value.
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
     * Returns the uploaded files from the $_FILES array.
     *
     * @return array<string, mixed> The uploaded files from the `$_FILES` array.
     */
    #[Override]
    public function getUploadedFiles(): array
    {
        return $this->files ??= static::buildFiles(static::normalizeFiles($_FILES));
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
     * Checks the `HTTPS` server param and common proxy headers (`X-Forwarded-Proto` and
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

        $xForwardedProto = $this->getHeaderLine('X-Forwarded-Proto');

        if ($xForwardedProto && strtolower($xForwardedProto) === 'https') {
            return true;
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
     * Returns the new ServerRequest instance with updated trusted proxies.
     *
     * @param string[] $trustedProxies The trusted proxy IPs.
     * @return static The new ServerRequest instance.
     */
    public function setTrustedProxies(array $trustedProxies): static
    {
        $temp = clone $this;

        $temp->trustedProxies = $trustedProxies;

        return $temp;
    }

    /**
     * Returns the new ServerRequest instance with proxy trust enabled or disabled.
     *
     * @param bool $trustProxy Whether proxy headers should be trusted.
     * @return static The new ServerRequest instance.
     */
    public function trustProxy(bool $trustProxy = true): static
    {
        $temp = clone $this;

        $temp->trustProxy = $trustProxy;

        return $temp;
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
     * Note: This implementation expects {@see UploadedFile} instances (and nested arrays of
     * them) and will throw if other values are provided.
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
     * Builds an array of UploadedFiles.
     *
     * @param array<string, mixed> $files The normalized files.
     * @return array<string, mixed> The UploadedFiles array.
     */
    protected static function buildFiles(array $files): array
    {
        return array_map(
            static function(array $data): array|UploadedFile {
                if (!isset($data['tmp_name'])) {
                    return static::buildFiles($data);
                }

                return new UploadedFile(
                    $data['tmp_name'],
                    $data['size'],
                    $data['error'],
                    $data['name'] ?? null,
                    $data['type'] ?? null
                );
            },
            $files
        );
    }

    /**
     * Builds headers from the $_SERVER data.
     *
     * @param array<string, mixed> $data The `$_SERVER` data.
     * @return array<string, mixed> The headers.
     */
    protected static function buildHeaders(array $data): array
    {
        $headers = [];

        $contentType = $data['CONTENT_TYPE'] ?? getenv('CONTENT_TYPE');

        if ($contentType) {
            $headers['Content-Type'] = $contentType;
        }

        foreach ($data as $key => $value) {
            if (!str_starts_with($key, 'HTTP_')) {
                continue;
            }

            $header = substr($key, 5) |> strtolower(...);
            $header = str_replace('_', ' ', $header) |> ucwords(...);
            $header = str_replace(' ', '-', $header);

            $headers[$header] = $value;
        }

        return $headers;
    }

    /**
     * Normalizes a file field array.
     *
     * @param array<string, mixed> $file The file field array.
     * @return array<string, mixed> The normalized file field array.
     */
    protected static function normalizeFileField(array $file): array
    {
        if (!isset($file['name']) || !is_array($file['name'])) {
            return $file;
        }

        $normalized = [];

        foreach ($file['name'] as $key => $value) {
            $data = [
                'name' => $value,
                'size' => $file['size'][$key],
                'error' => $file['error'][$key],
                'tmp_name' => $file['tmp_name'][$key] ?? null,
                'type' => $file['type'][$key] ?? null,
            ];

            if (is_array($value)) {
                $normalized[$key] = static::normalizeFileField($data);
            } else {
                $normalized[$key] = $data;
            }
        }

        return $normalized;
    }

    /**
     * Normalizes the $_FILES array.
     *
     * @param array<string, mixed> $files The $_FILES array.
     * @return array<string, mixed> The normalized files array.
     */
    protected static function normalizeFiles(array $files): array
    {
        $results = [];

        foreach ($files as $name => $file) {
            $results[$name] = static::normalizeFileField($file);
        }

        return $results;
    }

    /**
     * Validates uploaded files.
     *
     * @param array<string, mixed> $files The files to validate.
     * @param string $path The file path.
     *
     * @throws RuntimeException If an invalid uploaded file is found.
     */
    protected static function validateFiles(array $files, string $path = ''): void
    {
        foreach ($files as $key => $file) {
            if (is_array($file)) {
                static::validateFiles($file, $path.'.'.$key);

                continue;
            }

            if ($file instanceof UploadedFile) {
                continue;
            }

            throw new RuntimeException(sprintf(
                'Uploaded file `%s.%s` is not valid.',
                $path,
                $key
            ));
        }
    }
}
