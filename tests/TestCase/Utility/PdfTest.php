<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility;

use finfo;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Core\Traits\StaticMacroTrait;
use Fyre\Utility\Pdf;
use Override;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function array_diff;
use function class_uses;
use function mime_content_type;
use function mkdir;
use function rmdir;
use function touch;
use function unlink;

use const FILEINFO_MIME_TYPE;

final class PdfTest extends TestCase
{
    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Pdf::class)
        );
    }

    public function testGetBinaryPath(): void
    {
        $this->assertSame(
            'google-chrome',
            Pdf::getBinaryPath()
        );
    }

    public function testGetTimeout(): void
    {
        $this->assertSame(
            5000,
            Pdf::getTimeout()
        );
    }

    public function testMacro(): void
    {
        $this->assertEmpty(
            array_diff([MacroTrait::class, StaticMacroTrait::class], class_uses(Pdf::class))
        );
    }

    public function testPdfSaveHtml(): void
    {
        Pdf::createFromHtml('<h1>Test</h1>')
            ->save('tmp/test.pdf');

        $this->assertSame(
            'application/pdf',
            mime_content_type('tmp/test.pdf')
        );
    }

    public function testPdfSaveUrl(): void
    {
        Pdf::createFromUrl('tests/assets/test.html')
            ->save('tmp/test.pdf');

        $this->assertSame(
            'application/pdf',
            mime_content_type('tmp/test.pdf')
        );
    }

    public function testPdfSaveUrlExists(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File `tmp/test.pdf` already exists.');

        touch('tmp/test.pdf');

        Pdf::createFromUrl('tests/assets/test.html')
            ->save('tmp/test.pdf');
    }

    public function testPdfToBinary(): void
    {
        $pdf = Pdf::createFromHtml('<h1>Test</h1>')
            ->toBinary();

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($pdf);

        $this->assertSame(
            'application/pdf',
            $mimeType
        );
    }

    #[Override]
    protected function setUp(): void
    {
        Pdf::setBinaryPath('google-chrome');
        Pdf::setTimeout(5000);

        @mkdir('tmp');
    }

    #[Override]
    protected function tearDown(): void
    {
        @unlink('tmp/test.pdf');
        @rmdir('tmp');
    }
}
