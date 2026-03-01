<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Fyre\Http\Message;
use Fyre\Http\Response;
use Fyre\Http\Stream;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ResponseTest extends TestCase
{
    protected Response $response;

    public function testConstructor(): void
    {
        $response = new Response([
            'body' => 'test',
            'headers' => [
                'test' => 'value',
            ],
            'protocolVersion' => '2.0',
            'statusCode' => 400,
            'reasonPhrase' => 'Something went wrong',
        ]);

        $body = $response->getBody();

        $this->assertInstanceOf(
            Stream::class,
            $body
        );

        $this->assertSame(
            'test',
            $body->getContents()
        );

        $this->assertSame(
            [
                'value',
            ],
            $response->getHeader('test')
        );

        $this->assertSame(
            '2.0',
            $response->getProtocolVersion()
        );

        $this->assertSame(
            400,
            $response->getStatusCode()
        );

        $this->assertSame(
            'Something went wrong',
            $response->getReasonPhrase()
        );
    }

    public function testGetReasonPhrase(): void
    {
        $response1 = new Response();
        $response2 = $response1->withStatus(400);

        $this->assertSame(
            'Bad Request',
            $response2->getReasonPhrase()
        );
    }

    public function testGetStatusCode(): void
    {
        $response = new Response();

        $this->assertSame(
            200,
            $response->getStatusCode()
        );
    }

    public function testMessage(): void
    {
        $response = new Response();

        $this->assertInstanceOf(
            Message::class,
            $response
        );
    }

    public function testWithStatus(): void
    {
        $response1 = new Response();
        $response2 = $response1->withStatus(400, 'Something went wrong');

        $this->assertNotSame(
            $response1,
            $response2
        );

        $this->assertSame(
            400,
            $response2->getStatusCode()
        );

        $this->assertSame(
            'Something went wrong',
            $response2->getReasonPhrase()
        );
    }

    public function testWithStatusInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Status code `600` is not valid.');

        $response = new Response();
        $response->withStatus(600);
    }
}
