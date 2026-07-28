<?php
declare(strict_types=1);

namespace Fyre\Http\Client;

use Fyre\Http\Cookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

use function implode;
use function str_ends_with;
use function str_starts_with;
use function strlen;
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
        $this->cookies[$cookie->getId()] = $cookie;
    }

    /**
     * Returns the Cookie header for a URI.
     *
     * @param UriInterface $uri The URI.
     * @return string The Cookie header.
     */
    public function getHeader(UriInterface $uri): string
    {
        $values = [];

        foreach ($this->cookies as $cookie) {
            if ($cookie->isExpired()) {
                continue;
            }

            if ($uri->getScheme() === 'http' && $cookie->isSecure()) {
                continue;
            }

            $host = $uri->getHost();
            $domain = $cookie->getDomain();
            $cookiePath = $cookie->getPath() ?: '/';
            $requestPath = $uri->getPath() ?: '/';

            if ($domain) {
                if (str_starts_with($domain, '.')) {
                    $domain = substr($domain, 1);
                    if ($host !== $domain && !str_ends_with($host, '.'.$domain)) {
                        continue;
                    }
                } else if ($host !== $domain) {
                    continue;
                }
            }

            if ($cookiePath !== '/' && str_starts_with($requestPath, $cookiePath)) {
                $next = $requestPath[strlen($cookiePath)] ?? '';
                if ($next !== '' && $next !== '/') {
                    continue;
                }
            } else if ($cookiePath !== '/') {
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
        foreach ($response->getHeader('Set-Cookie') as $value) {
            Cookie::createFromHeaderString($value, [
                'domain' => $uri->getHost(),
                'path' => $uri->getPath() ?: '/',
            ]) |> $this->add(...);
        }
    }
}
