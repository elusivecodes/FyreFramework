<?php
declare(strict_types=1);

namespace Fyre\Http\Factories;

use Fyre\Http\Uri;
use Override;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Uri\Rfc3986\Uri as Rfc3986Uri;

use function is_numeric;
use function is_string;
use function parse_url;
use function preg_match;
use function preg_replace;
use function str_starts_with;
use function strcspn;
use function substr;

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

        $internalUri = new Rfc3986Uri('');

        if ($host !== '') {
            $internalUri = $internalUri
                ->withScheme($secure ? 'https' : 'http')
                ->withHost($host)
                ->withPort($port);
        }

        $requestUri = $serverParams['REQUEST_URI'] ?? null;

        if (is_string($requestUri) && $requestUri !== '') {
            if (str_starts_with($requestUri, '/')) {
                $pathLength = strcspn($requestUri, '?#');
                $path = substr($requestUri, 0, $pathLength);

                // Replace control characters with underscores to match parse_url().
                $path = (string) preg_replace('/[\x00-\x1F\x7F]/', '_', $path);
            } else {
                $path = (string) parse_url($requestUri, PHP_URL_PATH);
            }

            // withPath() validates the extracted path against RFC 3986.
            $internalUri = $internalUri->withPath($path);
        }

        $query = $serverParams['QUERY_STRING'] ?? null;

        if (is_string($query)) {
            if (str_starts_with($query, '?')) {
                $query = substr($query, 1);
            }

            if ($query !== '') {
                $internalUri = $internalUri->withQuery($query);
            }
        }

        return $internalUri->toString() |> $this->createUri(...);
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
