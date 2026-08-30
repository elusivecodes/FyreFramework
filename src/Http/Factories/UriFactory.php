<?php
declare(strict_types=1);

namespace Fyre\Http\Factories;

use Fyre\Http\Uri;
use Override;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

use function is_numeric;
use function is_string;
use function parse_url;
use function preg_match;

use const PHP_URL_PATH;

/**
 * Creates PSR-7 URIs.
 */
class UriFactory implements UriFactoryInterface
{
    /**
     * Creates a URI from server parameters.
     *
     * @param array<string, mixed> $serverParams The server parameters.
     * @param string $host The Host header value.
     * @param bool $secure Whether the request is secure.
     * @return UriInterface The new URI.
     */
    public function createFromServer(array $serverParams, string $host = '', bool $secure = false): UriInterface
    {
        $port = null;

        if ($host !== '' && preg_match('/\A(.*)\:(\d+)\z/', $host, $match)) {
            $host = $match[1];
            $port = (int) $match[2];
        } else if ($host === '') {
            $host = is_string($serverParams['SERVER_NAME'] ?? null) ?
                $serverParams['SERVER_NAME'] :
                '';

            if (is_numeric($serverParams['SERVER_PORT'] ?? null)) {
                $port = (int) $serverParams['SERVER_PORT'];
            }
        }

        $uri = $this->createUri();

        if ($host !== '') {
            $uri = $uri
                ->withScheme($secure ? 'https' : 'http')
                ->withHost($host)
                ->withPort($port);
        }

        $requestUri = $serverParams['REQUEST_URI'] ?? null;

        if (is_string($requestUri) && $requestUri !== '') {
            $path = (string) parse_url($requestUri, PHP_URL_PATH);
            $uri = $uri->withPath($path);
        }

        $query = $serverParams['QUERY_STRING'] ?? null;

        if (is_string($query) && $query !== '') {
            $uri = $uri->withQuery($query);
        }

        return $uri;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function createUri(string $uri = ''): UriInterface
    {
        return new Uri($uri);
    }
}
