<?php
declare(strict_types=1);

namespace Fyre\Http;

use DateTimeInterface;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\DateTime\DateTime;
use SimpleXMLElement;

use function array_values;
use function gmdate;
use function is_numeric;
use function is_string;
use function json_encode;
use function strtotime;

use const JSON_PRETTY_PRINT;

/**
 * Provides an HTTP response with convenience helpers.
 *
 * Adds helpers for setting common headers (e.g. `Date`, `Last-Modified`, cache control),
 * formatting JSON/XML bodies, and managing cookies separately from the `Set-Cookie` header.
 */
class ClientResponse extends Response
{
    use MacroTrait;

    protected const HEADER_FORMAT = 'D, d-M-Y H:i:s e';

    protected const MAX_BUFFER_SIZE = 8192;

    /**
     * @var array<string, Cookie>
     */
    protected array $cookies = [];

    /**
     * Constructs a ClientResponse.
     *
     * @param array<string, mixed> $options The response options.
     */
    public function __construct(array $options = [])
    {
        $options['headers'] ??= [];
        $options['headers']['Content-Type'] ??= 'text/html; charset=UTF-8';

        parent::__construct($options);
    }

    /**
     * Returns a cookie.
     *
     * Note: Cookies are keyed by id (name + domain + path), but this lookup matches by name
     * only and returns the first match.
     *
     * @param string $name The cookie name.
     * @return Cookie|null The Cookie instance, or null if no cookie matches the name.
     */
    public function getCookie(string $name): Cookie|null
    {
        foreach ($this->cookies as $cookie) {
            if ($cookie->getName() !== $name) {
                continue;
            }

            return $cookie;
        }

        return null;
    }

    /**
     * Returns all cookies.
     *
     * @return Cookie[] The cookies.
     */
    public function getCookies(): array
    {
        return array_values($this->cookies);
    }

    /**
     * Checks whether a cookie has been set.
     *
     * Note: This checks for any cookie with the given name (domain/path are ignored).
     *
     * @param string $name The cookie name.
     * @return bool Whether the cookie exists.
     */
    public function hasCookie(string $name): bool
    {
        foreach ($this->cookies as $cookie) {
            if ($cookie->getName() !== $name) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Returns the new ClientResponse instance with the `Content-Type` header.
     *
     * @param string $mimeType The MIME type.
     * @param string $charset The character set.
     * @return static The new ClientResponse instance with the updated content type.
     */
    public function withContentType(string $mimeType, string $charset = 'UTF-8'): static
    {
        return $this->withHeader('Content-Type', $mimeType.'; charset='.$charset);
    }

    /**
     * Returns the new ClientResponse instance with the added cookie.
     *
     * The cookie is stored in the response cookie collection and can be emitted by
     * {@see ResponseEmitter}. It is not added to the `Set-Cookie` header directly.
     *
     * Note: {@see ResponseEmitter} currently uses PHP's `setcookie()` without passing the
     * SameSite option, so the `$sameSite` value may not be sent when emitting.
     *
     * @param string $name The cookie name.
     * @param string $value The cookie value.
     * @param DateTime|int|null $expires The cookie expiration time (DateTime or UNIX timestamp).
     * @param string $path The cookie path.
     * @param string $domain The cookie domain.
     * @param bool $httpOnly Whether the cookie is HTTP only.
     * @param bool $secure Whether the cookie is secure.
     * @param string $sameSite The cookie SameSite attribute.
     * @return static The new ClientResponse instance with the added cookie.
     */
    public function withCookie(
        string $name,
        string $value,
        DateTime|int|null $expires = null,
        string $path = '/',
        string $domain = '',
        bool $httpOnly = false,
        bool $secure = false,
        string $sameSite = 'lax'
    ): static {
        if ($expires instanceof DateTime) {
            $expires = $expires->getTimestamp();
        }

        $temp = clone $this;

        $cookie = new Cookie($name, $value, [
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
            'httpOnly' => $httpOnly,
            'secure' => $secure,
            'sameSite' => $sameSite,
        ]);

        $temp->cookies[$cookie->getId()] = $cookie;

        return $temp;
    }

    /**
     * Returns the new ClientResponse instance with the `Date` header.
     *
     * Dates are formatted in UTC.
     *
     * @param DateTime|DateTimeInterface|int|string $date The date.
     * @return static The new ClientResponse instance with the updated date header.
     */
    public function withDate(DateTime|DateTimeInterface|int|string $date): static
    {
        $utcString = static::formatDateUTC($date);

        return $this->withHeader('Date', $utcString);
    }

    /**
     * Returns the new ClientResponse instance with cache disabling headers.
     *
     * Sets `Cache-Control` to `no-store`, `max-age=0`, and `no-cache`.
     *
     * @return static The new ClientResponse instance with cache disabling headers.
     */
    public function withDisabledCache(): static
    {
        return $this->withHeader('Cache-Control', ['no-store', 'max-age=0', 'no-cache']);
    }

    /**
     * Returns the new ClientResponse instance with an expired cookie.
     *
     * @param string $name The cookie name.
     * @param string $path The cookie path.
     * @param string $domain The cookie domain.
     * @param bool $httpOnly Whether the cookie is HTTP only.
     * @param bool $secure Whether the cookie is secure.
     * @param string $sameSite The cookie SameSite attribute.
     * @return static The new ClientResponse instance with the expired cookie.
     */
    public function withExpiredCookie(
        string $name,
        string $path = '/',
        string $domain = '',
        bool $httpOnly = false,
        bool $secure = false,
        string $sameSite = 'lax'
    ): static {
        return $this->withCookie($name, '', 1, $path, $domain, $httpOnly, $secure, $sameSite);
    }

    /**
     * Returns the new ClientResponse instance with a JSON body.
     *
     * Sets `Content-Type` to `application/json` and writes a pretty-printed JSON body.
     *
     * @param mixed $data The data to send.
     * @return static The new ClientResponse instance with the JSON body.
     */
    public function withJson(mixed $data): static
    {
        $data = (string) json_encode($data, JSON_PRETTY_PRINT);
        $body = Stream::createFromString($data);

        return $this
            ->withContentType('application/json')
            ->withBody($body);
    }

    /**
     * Returns the new ClientResponse instance with the `Last-Modified` header.
     *
     * Dates are formatted in UTC.
     *
     * @param DateTime|DateTimeInterface|int|string $date The date.
     * @return static The new ClientResponse instance with the updated last modified header.
     */
    public function withLastModified(DateTime|DateTimeInterface|int|string $date): static
    {
        $utcString = static::formatDateUTC($date);

        return $this->withHeader('Last-Modified', $utcString);
    }

    /**
     * Returns the new ClientResponse instance with an XML body.
     *
     * Sets `Content-Type` to `application/xml` and writes the XML body.
     *
     * @param SimpleXMLElement $data The SimpleXMLElement to send.
     * @return static The new ClientResponse instance with the XML body.
     */
    public function withXml(SimpleXMLElement $data): static
    {
        $data = (string) $data->asXML();
        $body = Stream::createFromString($data);

        return $this
            ->withContentType('application/xml')
            ->withBody($body);
    }

    /**
     * Formats a UTC date.
     *
     * Integers (and numeric strings) are treated as UNIX timestamps. Strings are parsed using
     * `strtotime()`.
     *
     * @param DateTime|DateTimeInterface|int|string $date The date to format.
     * @return string The formatted UTC date.
     */
    protected static function formatDateUTC(DateTime|DateTimeInterface|int|string $date): string
    {
        if (is_numeric($date)) {
            $timestamp = (int) $date;
        } else if (is_string($date)) {
            $timestamp = (int) strtotime($date);
        } else {
            $timestamp = $date->getTimestamp();
        }

        return gmdate(static::HEADER_FORMAT, $timestamp);
    }
}
