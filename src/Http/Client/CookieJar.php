<?php
declare(strict_types=1);

namespace Fyre\Http\Client;

use Fyre\Http\Cookie;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

use function implode;
use function in_array;
use function str_ends_with;
use function str_starts_with;
use function strrchr;
use function substr;

/**
 * Stores Cookies and builds Cookie headers for client requests.
 */
class CookieJar
{
    /**
     * @var array<string, Cookie>
     */
    protected array $cookies = [];

    /**
     * Adds a Cookie.
     *
     * @param Cookie $cookie The Cookie.
     */
    public function add(Cookie $cookie): void
    {
        $id = $cookie->getId();

        if ($cookie->isExpired()) {
            unset($this->cookies[$id]);

            return;
        }

        $this->cookies[$id] = $cookie;
    }

    /**
     * Returns the Cookie header for a URI.
     *
     * @param UriInterface $uri The URI.
     * @return string The Cookie header.
     */
    public function getHeader(UriInterface $uri): string
    {
        $scheme = $uri->getScheme();

        if (!in_array($scheme, ['http', 'https'], true)) {
            return '';
        }

        $host = $uri->getHost() |> static::normalizeHost(...);

        if ($host === null) {
            return '';
        }

        $requestPath = $uri->getPath() ?: '/';
        $values = [];

        foreach ($this->cookies as $cookie) {
            if ($cookie->isExpired()) {
                continue;
            }

            if ($scheme !== 'https' && $cookie->isSecure()) {
                continue;
            }

            $cookiePath = $cookie->getPath() ?: '/';

            if (
                !static::domainMatches($cookie, $host) ||
                !static::pathMatches($requestPath, $cookiePath)
            ) {
                continue;
            }

            $values[] = $cookie->getName().'='.$cookie->getValue();
        }

        return implode(';', $values);
    }

    /**
     * Stores Cookies from a Response.
     *
     * @param UriInterface $uri The response URI.
     * @param ResponseInterface $response The Response.
     */
    public function storeResponse(UriInterface $uri, ResponseInterface $response): void
    {
        $scheme = $uri->getScheme();
        $host = $uri->getHost() |> static::normalizeHost(...);

        if ($host === null || !in_array($scheme, ['http', 'https'], true)) {
            return;
        }

        foreach ($response->getHeader('Set-Cookie') as $value) {
            try {
                $cookie = Cookie::createFromHeaderString($value, [
                    'domain' => $host,
                    'path' => $uri->getPath() |> static::defaultPath(...),
                ]);
            } catch (InvalidArgumentException) {
                continue;
            }

            if (
                !$cookie->isDomainValid() ||
                ($cookie->isSecure() && $scheme !== 'https') ||
                !static::domainMatches($cookie, $host)
            ) {
                continue;
            }

            $this->add($cookie);
        }
    }

    /**
     * Returns the default cookie path for a request path.
     *
     * @param string $path The request path.
     * @return string The default cookie path.
     */
    protected static function defaultPath(string $path): string
    {
        if (!str_starts_with($path, '/')) {
            return '/';
        }

        return strrchr($path, '/', true) ?: '/';
    }

    /**
     * Checks whether a host matches a Cookie domain.
     *
     * @param Cookie $cookie The Cookie.
     * @param string $host The request host.
     * @return bool Whether the host matches the Cookie domain.
     */
    protected static function domainMatches(Cookie $cookie, string $host): bool
    {
        $domain = $cookie->getDomain();

        if ($host === $domain || $domain === '') {
            return true;
        }

        if ($cookie->isHostOnly()) {
            return false;
        }

        return str_ends_with($host, '.'.$domain);
    }

    /**
     * Normalizes a URI host for Cookie domain matching.
     *
     * @param string $host The URI host.
     * @return string|null The normalized host, or null if it is not valid.
     */
    protected static function normalizeHost(string $host): string|null
    {
        if (str_ends_with($host, '.')) {
            $host = substr($host, 0, -1);
        }

        [$host, $valid] = Cookie::normalizeDomain($host);

        return $valid && $host !== '' ?
            $host :
            null;
    }

    /**
     * Checks whether a request path matches a Cookie path.
     *
     * @param string $requestPath The request path.
     * @param string $cookiePath The Cookie path.
     * @return bool Whether the request path matches the Cookie path.
     */
    protected static function pathMatches(string $requestPath, string $cookiePath): bool
    {
        if ($requestPath === $cookiePath) {
            return true;
        }

        if (!str_ends_with($cookiePath, '/')) {
            $cookiePath .= '/';
        }

        return str_starts_with($requestPath, $cookiePath);
    }
}
