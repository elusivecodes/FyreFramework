<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ClientResponse;

use Fyre\Http\ClientResponse;

trait ContentTypeTestTrait
{
    public function testContentType(): void
    {
        $response = new ClientResponse();

        $this->assertSame(
            'text/html; charset=UTF-8',
            $response->getHeaderLine('Content-Type')
        );
    }

    public function testWithContentType(): void
    {
        $response1 = new ClientResponse();
        $response2 = $response1->withContentType('image/jpeg');

        $this->assertSame(
            'text/html; charset=UTF-8',
            $response1->getHeaderLine('Content-Type')
        );

        $this->assertSame(
            'image/jpeg; charset=UTF-8',
            $response2->getHeaderLine('Content-Type')
        );
    }

    public function testWithContentTypeWithCharset(): void
    {
        $response1 = new ClientResponse();
        $response2 = $response1->withContentType('image/jpeg', 'UTF-16');

        $this->assertSame(
            'text/html; charset=UTF-8',
            $response1->getHeaderLine('Content-Type')
        );

        $this->assertSame(
            'image/jpeg; charset=UTF-16',
            $response2->getHeaderLine('Content-Type')
        );
    }
}
