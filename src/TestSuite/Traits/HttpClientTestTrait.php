<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Traits;

use Closure;
use Fyre\Http\Client;
use Fyre\Http\Client\Response;
use PHPUnit\Framework\Attributes\After;

/**
 * Test case helpers for HTTP client assertions.
 *
 * Provides helpers for mocking {@see Client} responses and clearing mocks after each test.
 */
trait HttpClientTestTrait
{
    /**
     * Create a Response.
     *
     * @param int $statusCode The status code.
     * @param array<string, string|string[]> $headers The headers.
     * @param string $body The body.
     * @return Response The Response.
     */
    public function createResponse(int $statusCode = 200, array $headers = [], string $body = ''): Response
    {
        return new Response([
            'statusCode' => $statusCode,
            'headers' => $headers,
            'body' => $body,
        ]);
    }

    /**
     * Mock a client DELETE response.
     *
     * @param string $url The URL.
     * @param Response $response The Response.
     * @param Closure|null $match The match callback.
     */
    public function mockClientDelete(string $url, Response $response, Closure|null $match = null): void
    {
        Client::addMockResponse('DELETE', $url, $response, $match);
    }

    /**
     * Mock a client GET response.
     *
     * @param string $url The URL.
     * @param Response $response The Response.
     * @param Closure|null $match The match callback.
     */
    public function mockClientGet(string $url, Response $response, Closure|null $match = null): void
    {
        Client::addMockResponse('GET', $url, $response, $match);
    }

    /**
     * Mock a client PATCH response.
     *
     * @param string $url The URL.
     * @param Response $response The Response.
     * @param Closure|null $match The match callback.
     */
    public function mockClientPatch(string $url, Response $response, Closure|null $match = null): void
    {
        Client::addMockResponse('PATCH', $url, $response, $match);
    }

    /**
     * Mock a client POST response.
     *
     * @param string $url The URL.
     * @param Response $response The Response.
     * @param Closure|null $match The match callback.
     */
    public function mockClientPost(string $url, Response $response, Closure|null $match = null): void
    {
        Client::addMockResponse('POST', $url, $response, $match);
    }

    /**
     * Mock a client PUT response.
     *
     * @param string $url The URL.
     * @param Response $response The Response.
     * @param Closure|null $match The match callback.
     */
    public function mockClientPut(string $url, Response $response, Closure|null $match = null): void
    {
        Client::addMockResponse('PUT', $url, $response, $match);
    }

    /**
     * Clear mock responses.
     */
    #[After]
    protected function clearMockResponses(): void
    {
        Client::clearMockResponses();
    }
}
