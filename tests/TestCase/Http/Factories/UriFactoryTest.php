<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Factories;

use Fyre\Http\Factories\UriFactory;
use Fyre\Http\Uri;
use Override;
use PHPUnit\Framework\TestCase;

final class UriFactoryTest extends TestCase
{
    protected UriFactory $uriFactory;

    public function testCreateFromServer(): void
    {
        $uri = $this->uriFactory->createFromServer(
            [
                'QUERY_STRING' => 'page=2',
                'REQUEST_URI' => '/documents?page=2',
            ],
            'example.com:8443',
            true
        );

        $this->assertSame(
            'https://example.com:8443/documents?page=2',
            (string) $uri
        );
    }

    public function testCreateFromServerName(): void
    {
        $uri = $this->uriFactory->createFromServer([
            'REQUEST_URI' => '/documents',
            'SERVER_NAME' => 'example.com',
            'SERVER_PORT' => '8080',
        ]);

        $this->assertSame(
            'http://example.com:8080/documents',
            (string) $uri
        );
    }

    public function testCreateUri(): void
    {
        $uri = $this->uriFactory->createUri('https://example.com/path?query=1');

        $this->assertInstanceOf(
            Uri::class,
            $uri
        );

        $this->assertSame(
            'https://example.com/path?query=1',
            (string) $uri
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->uriFactory = new UriFactory();
    }
}
