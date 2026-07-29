<?php
declare(strict_types=1);

namespace Fyre\Http\Cookie;

use Fyre\Core\Traits\DebugTrait;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

use function array_filter;
use function array_key_first;
use function count;
use function filter_var;
use function implode;
use function in_array;
use function str_ends_with;
use function str_starts_with;
use function strlen;
use function strrchr;
use function substr;
use function trim;
use function usort;

use const FILTER_VALIDATE_IP;

/**
 * Stores Cookies and builds Cookie headers for client requests.
 */
class CookieJar
{
    use DebugTrait;

    protected const MAX_COOKIE_HEADER_SIZE = 16384;

    protected const MAX_COOKIE_SIZE = 4096;

    protected const MAX_COOKIES = 3000;

    protected const MAX_COOKIES_PER_DOMAIN = 180;

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

        $this->removeExpired();

        if ($cookie->toHeaderString() |> strlen(...) > static::MAX_COOKIE_SIZE) {
            return;
        }

        if (isset($this->cookies[$id])) {
            unset($this->cookies[$id]);
            $this->cookies[$id] = $cookie;

            return;
        }

        $domain = $cookie->getDomain();
        $domainCookies = array_filter(
            $this->cookies,
            static fn(Cookie $cookie): bool => $cookie->getDomain() === $domain
        );

        if (count($domainCookies) >= static::MAX_COOKIES_PER_DOMAIN) {
            $oldestId = array_key_first($domainCookies);

            if ($oldestId !== null) {
                unset($this->cookies[$oldestId]);
            }
        }

        if (count($this->cookies) >= static::MAX_COOKIES) {
            $oldestId = array_key_first($this->cookies);

            if ($oldestId !== null) {
                unset($this->cookies[$oldestId]);
            }
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

        $requestPath = $uri->getPath();
        $cookies = [];

        foreach ($this->cookies as $cookie) {
            if ($cookie->isExpired()) {
                continue;
            }

            if ($scheme !== 'https' && $cookie->isSecure()) {
                continue;
            }

            if (
                !static::domainMatches($cookie, $host) ||
                !static::pathMatches($requestPath, $cookie->getPath())
            ) {
                continue;
            }

            $cookies[] = $cookie;
        }

        usort(
            $cookies,
            static function(Cookie $a, Cookie $b): int {
                $aPath = $a->getPath() ?: '/';
                $bPath = $b->getPath() ?: '/';

                return strlen($bPath) <=> strlen($aPath);
            }
        );

        $length = 0;
        $values = [];

        foreach ($cookies as $cookie) {
            $value = $cookie->getName().'='.$cookie->getValue();
            $nextLength = $length + ($values === [] ? 0 : 2) + strlen($value);

            if ($nextLength > static::MAX_COOKIE_HEADER_SIZE) {
                continue;
            }

            $values[] = $value;
            $length = $nextLength;
        }

        return implode('; ', $values);
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

        $isSecureOrigin = $scheme === 'https';

        $cookieHeaders = $response->getHeader('Set-Cookie');

        foreach ($cookieHeaders as $value) {
            try {
                $cookie = Cookie::createFromHeaderString($value, [
                    'domain' => $host,
                    'path' => $uri->getPath() |> static::defaultPath(...),
                ]);
            } catch (InvalidArgumentException) {
                continue;
            }

            if (!$cookie->isDomainValid()) {
                continue;
            }

            $isSecureCookie = $cookie->isSecure();

            if (!$isSecureOrigin && $isSecureCookie) {
                continue;
            }

            $name = $cookie->getName();

            if (
                str_starts_with($name, '__Secure-') &&
                !$isSecureCookie
            ) {
                continue;
            }

            if (
                str_starts_with($name, '__Host-') &&
                (
                    !$isSecureCookie ||
                    !$cookie->isHostOnly() ||
                    $cookie->getPath() !== '/'
                )
            ) {
                continue;
            }

            if (!static::domainMatches($cookie, $host)) {
                continue;
            }

            if (
                !$isSecureOrigin &&
                $this->overlapsSecureCookie($cookie)
            ) {
                continue;
            }

            $this->add($cookie);
        }
    }

    /**
     * Checks whether a Cookie overlaps a stored Secure Cookie.
     *
     * @param Cookie $cookie The Cookie.
     * @return bool Whether the Cookie overlaps a stored Secure Cookie.
     */
    protected function overlapsSecureCookie(Cookie $cookie): bool
    {
        foreach ($this->cookies as $existing) {
            if (
                !$existing->isSecure() ||
                $existing->isExpired() ||
                $existing->getName() !== $cookie->getName() ||
                !static::domainsOverlap($existing, $cookie) ||
                !static::pathsOverlap($existing->getPath(), $cookie->getPath())
            ) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Removes expired Cookies.
     */
    protected function removeExpired(): void
    {
        foreach ($this->cookies as $id => $cookie) {
            if ($cookie->isExpired()) {
                unset($this->cookies[$id]);
            }
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

        if (
            $cookie->isHostOnly() ||
            filter_var(trim($host, '[]'), FILTER_VALIDATE_IP) !== false
        ) {
            return false;
        }

        return str_ends_with($host, '.'.$domain);
    }

    /**
     * Checks whether two Cookie domains overlap.
     *
     * @param Cookie $first The first Cookie.
     * @param Cookie $second The second Cookie.
     * @return bool Whether the Cookie domains overlap.
     */
    protected static function domainsOverlap(Cookie $first, Cookie $second): bool
    {
        return static::domainMatches($first, $second->getDomain()) ||
            static::domainMatches($second, $first->getDomain());
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
        $requestPath = $requestPath ?: '/';
        $cookiePath = $cookiePath ?: '/';

        if ($requestPath === $cookiePath) {
            return true;
        }

        if (!str_ends_with($cookiePath, '/')) {
            $cookiePath .= '/';
        }

        return str_starts_with($requestPath, $cookiePath);
    }

    /**
     * Checks whether two Cookie paths overlap.
     *
     * @param string $first The first Cookie path.
     * @param string $second The second Cookie path.
     * @return bool Whether the Cookie paths overlap.
     */
    protected static function pathsOverlap(string $first, string $second): bool
    {
        return static::pathMatches($first, $second) ||
            static::pathMatches($second, $first);
    }
}
