<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\UploadedFile;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function class_uses;

use const UPLOAD_ERR_NO_FILE;

final class UploadedFileTest extends TestCase
{
    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(UploadedFile::class)
        );
    }

    public function testGetClientFilename(): void
    {
        $file = new UploadedFile(
            '/tmp/php1',
            1,
            UPLOAD_ERR_OK,
            'test.txt',
            'text/plain'
        );

        $this->assertSame(
            'test.txt',
            $file->getClientFilename()
        );
    }

    public function testGetClientMediaType(): void
    {
        $file = new UploadedFile(
            '/tmp/php1',
            1,
            UPLOAD_ERR_OK,
            'test.txt',
            'text/plain'
        );

        $this->assertSame(
            'text/plain',
            $file->getClientMediaType()
        );
    }

    public function testGetError(): void
    {
        $file = new UploadedFile(
            '/tmp/php1',
            1,
            UPLOAD_ERR_NO_FILE,
            'test.txt',
            'text/plain'
        );

        $this->assertSame(
            UPLOAD_ERR_NO_FILE,
            $file->getError()
        );
    }

    public function testMoveToInvalid(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to move upload `test.txt` to `tmp/php1`.');

        $file = new UploadedFile(
            '/tmp/php1',
            1,
            UPLOAD_ERR_OK,
            'test.txt',
            'text/plain'
        );

        $file->moveTo('tmp/php1');
    }
}
