<?php
declare(strict_types=1);

namespace Fyre\Http\Client;

use Fyre\Core\Traits\MacroTrait;
use Fyre\Http\Cookie;
use Fyre\Http\Response as HttpResponse;
use RuntimeException;

use function array_values;
use function in_array;
use function json_decode;
use function json_last_error;

/**
 * Extends {@see HttpResponse} with helpers for parsing `Set-Cookie` headers, detecting
 * redirects, and decoding JSON response bodies.
 */
class Response extends HttpResponse
{
    use MacroTrait;

    protected const REDIRECT_CODES = [
        301,
        302,
        303,
        307,
        308,
    ];

    /**
     * @var array<string, Cookie>|null
     */
    protected array|null $cookies = null;

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
        foreach ($this->loadCookies() as $cookie) {
            if ($cookie->getName() === $name) {
                return $cookie;
            }
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
        return $this->loadCookies() |> array_values(...);
    }

    /**
     * Returns the JSON decoded body.
     *
     * Note: This expects the body to be a JSON object/array. A JSON `null` literal is treated
     * as an empty array.
     *
     * @return array<mixed> The decoded body.
     *
     * @throws RuntimeException If the body is not valid JSON.
     */
    public function getJson(): array
    {
        $data = json_decode((string) $this->body, true) ?? [];

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('The response body is not valid.');
        }

        return $data;
    }

    /**
     * Checks whether the response is OK.
     *
     * Returns true for successful and redirect responses (`200`-`399`).
     *
     * @return bool Whether the response is OK.
     */
    public function isOk(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode <= 399;
    }

    /**
     * Checks whether the response is a redirect.
     *
     * Requires both a redirect status code and a non-empty `Location` header.
     *
     * @return bool Whether the response is a redirect.
     */
    public function isRedirect(): bool
    {
        return in_array($this->statusCode, static::REDIRECT_CODES, true) && $this->getHeaderLine('Location');
    }

    /**
     * Checks whether the response is a success.
     *
     * @return bool Whether the response is a success.
     */
    public function isSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode <= 299;
    }

    /**
     * Loads Cookies.
     *
     * Parses `Set-Cookie` headers and caches the results by cookie id.
     *
     * @return array<string, Cookie> The cookies keyed by id.
     */
    protected function loadCookies(): array
    {
        if ($this->cookies !== null) {
            return $this->cookies;
        }

        $this->cookies = [];
        $header = $this->getHeader('Set-Cookie');

        foreach ($header as $value) {
            $cookie = Cookie::createFromHeaderString($value);
            $this->cookies[$cookie->getId()] = $cookie;
        }

        return $this->cookies;
    }
}
