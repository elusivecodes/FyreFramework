<?php
declare(strict_types=1);

namespace Fyre\Http\Client\Handlers;

use Closure;
use Fyre\Http\Client\ClientHandler;
use Fyre\Http\Client\Response;
use Override;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

use function preg_match;
use function preg_quote;
use function sprintf;
use function str_contains;
use function str_replace;

/**
 * Returns pre-configured {@see Response} instances for matching method/URL requests. Useful
 * for testing client logic without performing network I/O.
 */
class MockHandler extends ClientHandler
{
    /**
     * @var array<string, mixed>[]
     */
    protected array $responses = [];

    /**
     * Adds a mock response.
     *
     * Note: When a response matches, it is moved to the end of the internal list so recently
     * used mocks are checked later.
     *
     * @param string $method The method.
     * @param string $url The URL.
     * @param Response $response The Response.
     * @param (Closure(RequestInterface): bool)|null $match The optional match callback.
     */
    public function addResponse(string $method, string $url, Response $response, Closure|null $match = null): void
    {
        $this->responses[] = [
            'method' => $method,
            'url' => $url,
            'response' => $response,
            'match' => $match,
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException If no mock response is found.
     */
    #[Override]
    public function send(RequestInterface $request, array $options = []): Response
    {
        $method = $request->getMethod();
        $url = (string) $request->getUri();

        foreach ($this->responses as $i => $mock) {
            if ($mock['method'] !== $method) {
                continue;
            }

            if (!static::urlMatches($url, $mock['url'])) {
                continue;
            }

            if ($mock['match'] !== null && !$mock['match']($request)) {
                continue;
            }

            unset($this->responses[$i]);
            $this->responses[] = $mock;

            return $mock['response'];
        }

        throw new RuntimeException(sprintf(
            'No mock response found for `%s` (%s).',
            $url,
            $method
        ));
    }

    /**
     * Checks whether a request URL matches a mock URL.
     *
     * The mock URL may contain `*` wildcards, which match any character sequence.
     *
     * @param string $requestUrl The request URL.
     * @param string $mockUrl The mock URL.
     * @return bool Whether the URLs match.
     */
    protected static function urlMatches(string $requestUrl, string $mockUrl): bool
    {
        if (!str_contains($mockUrl, '*')) {
            return $requestUrl === $mockUrl;
        }

        $pattern = '/^'.str_replace('\*', '.*', preg_quote($mockUrl, '/')).'$/';

        return preg_match($pattern, $requestUrl) === 1;
    }
}
