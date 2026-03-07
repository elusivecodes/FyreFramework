<?php
declare(strict_types=1);

namespace Fyre\Http;

use Fyre\Core\Traits\DebugTrait;
use InvalidArgumentException;

use function array_map;
use function array_replace;
use function array_shift;
use function explode;
use function gmdate;
use function implode;
use function in_array;
use function rawurlencode;
use function sprintf;
use function str_contains;
use function str_replace;
use function strtolower;
use function strtotime;
use function time;
use function trim;
use function urldecode;

/**
 * Represents a cookie and can parse/format `Set-Cookie` header values.
 */
class Cookie
{
    use DebugTrait;

    protected const RESERVED_CHARS = ['=', ',', ';', ' ', "\t", "\r", "\n", "\v", "\f"];

    /**
     * @var array<string, mixed>
     */
    protected static array $defaults = [
        'expires' => null,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httpOnly' => false,
        'sameSite' => 'lax',
    ];

    protected string $domain;

    protected int|null $expires;

    protected bool $httpOnly;

    protected string $path;

    /**
     * @var 'lax'|'none'|'strict'
     */
    protected string $sameSite;

    protected bool $secure;

    /**
     * Creates a Cookie from a header string.
     *
     * Parses a `Set-Cookie` header value and applies any provided default options.
     * Header attributes are parsed case-insensitively.
     *
     * Note: `Max-Age` takes precedence over `Expires`. If `Expires` cannot be parsed,
     * `strtotime()` may return `false` (which becomes `0` after casting to int).
     *
     * @param string $string The `Set-Cookie` header value.
     * @param array<string, mixed> $options The default cookie options to apply.
     * @return static The new Cookie instance.
     */
    public static function createFromHeaderString(string $string, array $options = []): static
    {
        $parts = explode(';', $string);

        $nameValue = array_shift($parts);
        $nameValue = explode('=', $nameValue, 2);

        $name = array_shift($nameValue) |> trim(...) |> urldecode(...);
        $value = (array_shift($nameValue) ?? '') |> trim(...) |> urldecode(...);

        foreach ($parts as $part) {
            if (str_contains($part, '=')) {
                [$key, $val] = explode('=', $part, 2);
            } else {
                $key = $part;
                $val = true;
            }

            $key = trim($key) |> strtolower(...);
            $key = match ($key) {
                'httponly' => 'httpOnly',
                'samesite' => 'sameSite',
                default => $key
            };

            $options[$key] = $val;
        }

        if (isset($options['max-age'])) {
            $options['expires'] = time() + (int) $options['max-age'];
        } else if (isset($options['expires'])) {
            $options['expires'] = (int) strtotime((string) $options['expires']);
        }

        unset($options['max-age']);

        return new static($name, $value, $options);
    }

    /**
     * Constructs a Cookie.
     *
     * @param string $name The cookie name.
     * @param string $value The cookie value.
     * @param array<string, mixed> $options The options for the cookie.
     *
     * @throws InvalidArgumentException If the same site option is not valid.
     */
    public function __construct(
        protected string $name,
        protected string $value = '',
        array $options = []
    ) {
        $options = array_replace(static::$defaults, $options);

        $this->expires = $options['expires'];
        $this->path = $options['path'];
        $this->domain = $options['domain'];
        $this->secure = $options['secure'];
        $this->httpOnly = $options['httpOnly'];

        $sameSite = strtolower((string) $options['sameSite']);

        if (!in_array($sameSite, ['lax', 'strict', 'none'], true)) {
            throw new InvalidArgumentException(sprintf(
                'Same site `%s` is not valid.',
                $sameSite
            ));
        }

        $this->sameSite = $sameSite;
    }

    /**
     * Returns the cookie header string.
     *
     * @return string The cookie header string.
     */
    public function __toString(): string
    {
        return $this->toHeaderString();
    }

    /**
     * Returns the cookie domain.
     *
     * @return string The cookie domain.
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Returns the cookie expires timestamp.
     *
     * @return int|null The cookie expires timestamp.
     */
    public function getExpires(): int|null
    {
        return $this->expires;
    }

    /**
     * Returns the unique cookie identifier.
     *
     * The identifier is based on the cookie name, domain, and path.
     *
     * @return string The unique cookie identifier.
     */
    public function getId(): string
    {
        return implode(',', [$this->name, $this->domain, $this->path]);
    }

    /**
     * Returns the cookie name.
     *
     * @return string The cookie name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the cookie path.
     *
     * @return string The cookie path.
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Returns the cookie SameSite attribute.
     *
     * @return 'lax'|'none'|'strict' The cookie SameSite attribute.
     */
    public function getSameSite(): string
    {
        return $this->sameSite;
    }

    /**
     * Returns the cookie value.
     *
     * @return string The cookie value.
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Checks whether the cookie has expired.
     *
     * @return bool Whether the cookie has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires !== null && $this->expires < time();
    }

    /**
     * Checks whether the cookie is HTTP only.
     *
     * @return bool Whether the cookie is HTTP only.
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * Checks whether the cookie is secure.
     *
     * @return bool Whether the cookie is secure.
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * Returns the cookie header string.
     *
     * Formats a `Set-Cookie` header value with attribute tokens in lower-case (e.g. `secure`,
     * `httponly`, `samesite`).
     *
     * @return string The cookie header string.
     */
    public function toHeaderString(): string
    {
        $result = '';

        $replacements = array_map(rawurlencode(...), static::RESERVED_CHARS);
        $result = str_replace(static::RESERVED_CHARS, $replacements, $this->name);
        $result .= '=';
        $result .= rawurlencode($this->value);

        if ($this->expires !== null) {
            $result .= '; expires='.gmdate('D, d M Y H:i:s T', $this->expires);
        }

        if ($this->path) {
            $result .= '; path='.$this->path;
        }

        if ($this->domain) {
            $result .= '; domain='.$this->domain;
        }

        if ($this->secure) {
            $result .= '; secure';
        }

        if ($this->httpOnly) {
            $result .= '; httponly';
        }

        if ($this->sameSite) {
            $result .= '; samesite='.$this->sameSite;
        }

        return $result;
    }
}
