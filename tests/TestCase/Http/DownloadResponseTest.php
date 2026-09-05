<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Fyre\Http\ClientResponse;
use Fyre\Http\DownloadResponse;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function file_get_contents;

final class DownloadResponseTest extends TestCase
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function filenameProvider(): array
    {
        return [
            'plain' => ['file.txt', 'attachment; filename="file.txt"'],
            'spaces' => ['report final.pdf', 'attachment; filename="report final.pdf"'],
            'quotes' => ['report "final".pdf', 'attachment; filename="report \\"final\\".pdf"'],
            'backslash' => ['report\\final.pdf', 'attachment; filename="report\\\\final.pdf"'],
            'parameter injection' => ['report"; filename="other.pdf', 'attachment; filename="report\\"; filename=\\"other.pdf"'],
            'percent' => ['100%25.txt', 'attachment; filename="100%25.txt"'],
            'unicode' => ['résumé.pdf', 'attachment; filename="resume.pdf"; filename*=UTF-8\'\'r%C3%A9sum%C3%A9.pdf'],
            'unicode quotes' => ['résumé "final".pdf', 'attachment; filename="resume \\"final\\".pdf"; filename*=UTF-8\'\'r%C3%A9sum%C3%A9%20%22final%22.pdf'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidHeaderFilenameProvider(): array
    {
        return [
            'newline' => ["report\n.txt"],
            'carriage return' => ["report\r.txt"],
            'null byte' => ["report\0.txt"],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function unicodeFilenameProvider(): array
    {
        return [
            'chinese' => ['报告.pdf', '%E6%8A%A5%E5%91%8A.pdf'],
            'emoji' => ['📄.pdf', '%F0%9F%93%84.pdf'],
        ];
    }

    public function testCreateFromString(): void
    {
        $data = file_get_contents('tests/assets/test.txt') ?: '';

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

    #[DataProvider('filenameProvider')]
    public function testCreateFromStringFilename(string $filename, string $expected): void
    {
        $response = DownloadResponse::createFromString('test', $filename, 'text/plain');

        $this->assertSame(
            $expected,
            $response->getHeaderLine('Content-Disposition')
        );
    }

    #[DataProvider('filenameProvider')]
    public function testFilename(string $filename, string $expected): void
    {
        $response = DownloadResponse::createFromFile('tests/assets/test.txt', $filename);

        $this->assertSame(
            $expected,
            $response->getHeaderLine('Content-Disposition')
        );
    }

    public function testFilenameHeaderOverride(): void
    {
        $response = DownloadResponse::createFromString('test', 'résumé.pdf', 'text/plain', [
            'headers' => [
                'Content-Disposition' => 'inline; filename="custom.pdf"',
            ],
        ]);

        $this->assertSame(
            'inline; filename="custom.pdf"',
            $response->getHeaderLine('Content-Disposition')
        );
    }

    #[DataProvider('invalidHeaderFilenameProvider')]
    public function testFilenameInvalidHeader(string $filename): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Header value `attachment; filename="'.$filename.'"` is not valid.');

        DownloadResponse::createFromString('test', $filename, 'text/plain');
    }

    public function testFilenameInvalidUtf8(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Download filename must be valid UTF-8.');

        DownloadResponse::createFromString('test', "report\xFF.txt", 'text/plain');
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
        $this->expectExceptionMessageIs('File `tests/Mock/invalid.txt` does not exist.');

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

    #[DataProvider('unicodeFilenameProvider')]
    public function testUnicodeFilename(string $filename, string $expected): void
    {
        $response = DownloadResponse::createFromString('test', $filename, 'text/plain');

        $this->assertStringEndsWith(
            '; filename*=UTF-8\'\''.$expected,
            $response->getHeaderLine('Content-Disposition')
        );
    }
}
