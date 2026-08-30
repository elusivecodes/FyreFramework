<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Factories;

use Fyre\Http\ClientResponse;
use Fyre\Http\Factories\ResponseFactory;
use Override;
use PHPUnit\Framework\TestCase;

final class ResponseFactoryTest extends TestCase
{
    protected ResponseFactory $responseFactory;

    public function testCreateFromOptions(): void
    {
        $response = $this->responseFactory->createFromOptions([
            'body' => 'test',
        ]);

        $this->assertSame(
            'test',
            (string) $response->getBody()
        );
    }

    public function testCreateResponse(): void
    {
        $response = $this->responseFactory->createResponse(202, 'Accepted for processing');

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertSame(
            202,
            $response->getStatusCode()
        );

        $this->assertSame(
            'Accepted for processing',
            $response->getReasonPhrase()
        );
    }

    public function testCreateResponseDefault(): void
    {
        $response = $this->responseFactory->createResponse();

        $this->assertSame(
            200,
            $response->getStatusCode()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->responseFactory = new ResponseFactory();
    }
}
