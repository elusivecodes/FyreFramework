<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Fyre\Http\ClientResponse;
use Fyre\Http\DownloadResponse;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function file_get_contents;

final class DownloadResponseTest extends TestCase
{
    public function testCreateFromString(): void
    {
        $data = file_get_contents('tests/assets/test.txt');

        $response = DownloadResponse::createFromString($data, 'file.txt');

        $this->assertInstanceOf(
            DownloadResponse::class,
            $response
        );

        $this->assertSame(
            'text/plain; charset=UTF-8',
            $response->getHeaderLine('Content-Type')
        );

        $this->assertSame(
            'attachment; filename="file.txt"',
            $response->getHeaderLine('Content-Disposition')
        );

        $this->assertSame(
            '15',
            $response->getHeaderLine('Content-Length')
        );

        $this->assertSame(
            'This is a test.',
            $response->getBody()->getContents()
        );
    }

    public function testFilename(): void
    {
        $response = DownloadResponse::createFromFile('tests/assets/test.txt', 'file.txt');

        $this->assertSame(
            'attachment; filename="file.txt"',
            $response->getHeaderLine('Content-Disposition')
        );
    }

    public function testHeaders(): void
    {
        $response = DownloadResponse::createFromFile('tests/assets/test.txt');

        $this->assertSame(
            'text/plain; charset=UTF-8',
            $response->getHeaderLine('Content-Type')
        );

        $this->assertSame(
            'attachment; filename="test.txt"',
            $response->getHeaderLine('Content-Disposition')
        );

        $this->assertSame(
            '0',
            $response->getHeaderLine('Expires')
        );

        $this->assertSame(
            'binary',
            $response->getHeaderLine('Content-Transfer-Encoding')
        );

        $this->assertSame(
            '15',
            $response->getHeaderLine('Content-Length')
        );

        $this->assertSame(
            'private, no-transform, no-store, must-revalidate',
            $response->getHeaderLine('Cache-Control')
        );
    }

    public function testInvalidFile(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('File `tests/Mock/invalid.txt` does not exist.');

        $response = DownloadResponse::createFromFile('tests/Mock/invalid.txt');
    }

    public function testMimeType(): void
    {
        $response = DownloadResponse::createFromFile('tests/assets/test.txt', mimeType: 'application/octet-stream');

        $this->assertSame(
            'application/octet-stream; charset=UTF-8',
            $response->getHeaderLine('Content-Type')
        );
    }

    public function testResponse(): void
    {
        $response = DownloadResponse::createFromFile('tests/assets/test.txt');

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );
    }
}
