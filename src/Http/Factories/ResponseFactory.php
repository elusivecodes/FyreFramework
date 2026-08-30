<?php
declare(strict_types=1);

namespace Fyre\Http\Factories;

use Fyre\Http\ClientResponse;
use Override;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * Creates PSR-7 responses.
 */
class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * Creates a ClientResponse from response options.
     *
     * @param array<string, mixed> $options The response options.
     * @return ClientResponse The new ClientResponse.
     */
    public function createFromOptions(array $options = []): ClientResponse
    {
        return new ClientResponse($options);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ClientResponse
    {
        return $this->createFromOptions([
            'statusCode' => $code,
            'reasonPhrase' => $reasonPhrase,
        ]);
    }
}
