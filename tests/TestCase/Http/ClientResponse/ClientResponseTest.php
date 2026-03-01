<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ClientResponse;

use Fyre\Core\Traits\MacroTrait;
use Fyre\Http\ClientResponse;
use PHPUnit\Framework\TestCase;
use SimpleXMLElement;

use function class_uses;
use function json_decode;

final class ClientResponseTest extends TestCase
{
    use ContentTypeTestTrait;
    use CookieTestTrait;
    use DateTestTrait;

    protected ClientResponse $response;

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(ClientResponse::class)
        );
    }

    public function testWithDisabledCache(): void
    {
        $response1 = new ClientResponse();
        $response2 = $response1->withDisabledCache();

        $this->assertSame(
            '',
            $response1->getHeaderLine('Cache-Control')
        );

        $this->assertSame(
            'no-store, max-age=0, no-cache',
            $response2->getHeaderLine('Cache-Control')
        );
    }

    public function testWithJson(): void
    {
        $response1 = new ClientResponse();
        $response2 = $response1->withJson(['a' => 1]);

        $this->assertSame(
            '',
            $response1->getBody()->getContents()
        );

        $this->assertSame(
            'text/html; charset=UTF-8',
            $response1->getHeaderLine('Content-Type')
        );

        $this->assertSame(
            [
                'a' => 1,
            ],
            json_decode($response2->getBody()->getContents(), true)
        );

        $this->assertSame(
            'application/json; charset=UTF-8',
            $response2->getHeaderLine('Content-Type')
        );
    }

    public function testWithXml(): void
    {
        $xml = new SimpleXMLElement('<books><book><title>Test</title></book></books>');

        $response1 = new ClientResponse();
        $response2 = $response1->withXml($xml);

        $this->assertSame(
            '',
            $response1->getBody()->getContents()
        );

        $this->assertSame(
            'text/html; charset=UTF-8',
            $response1->getHeaderLine('Content-Type')
        );

        $this->assertSame(
            $xml->asXML(),
            $response2->getBody()->getContents()
        );

        $this->assertSame(
            'application/xml; charset=UTF-8',
            $response2->getHeaderLine('Content-Type')
        );
    }
}
