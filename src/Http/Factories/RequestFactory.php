<?php
declare(strict_types=1);

namespace Fyre\Http\Factories;

use Fyre\Http\Client\Request;
use Override;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * Creates PSR-7 requests.
 */
class RequestFactory implements RequestFactoryInterface
{
    /**
     * Creates a client Request from request options.
     *
     * @param string|UriInterface|null $uri The request URI.
     * @param array<string, mixed> $options The request options.
     * @return Request The new Request.
     */
    public function createFromOptions(string|UriInterface|null $uri = null, array $options = []): Request
    {
        return new Request($uri, $options);
    }

    /**
     * {@inheritDoc}
     *
     * @param string|UriInterface $uri The request URI.
     */
    #[Override]
    public function createRequest(string $method, $uri): Request
    {
        return $this->createFromOptions($uri, [
            'method' => $method,
        ]);
    }
}
