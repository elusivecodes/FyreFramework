<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Closure;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\UploadedFile;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function assert;
use function class_uses;
use function file_put_contents;
use function tempnam;
use function unlink;

use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_OK;

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

    public function testGetSize(): void
    {
        $file = new UploadedFile(
            '/tmp/php1',
            1,
            UPLOAD_ERR_OK,
            'test.txt',
            'text/plain'
        );

        $this->assertSame(
            1,
            $file->getSize()
        );
    }

    public function testGetStream(): void
    {
        $filePath = tempnam('/tmp', 'uploaded-file');

        assert($filePath !== false);

        file_put_contents($filePath, 'This is a test.');

        $file = new UploadedFile(
            $filePath,
            15,
            UPLOAD_ERR_OK,
            'test.txt',
            'text/plain'
        );

        $stream = $file->getStream();

        $this->assertSame(
            $stream,
            $file->getStream()
        );

        $this->assertSame(
            'This is a test.',
            $stream->getContents()
        );

        @unlink($filePath);
    }

    public function testMoveTo(): void
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

    public function testMoveToMoved(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Upload already moved: test.txt');

        $file = new UploadedFile(
            '/tmp/php1',
            1,
            UPLOAD_ERR_OK,
            'test.txt',
            'text/plain'
        );

        Closure::bind(function(): void {
            $this->hasMoved = true;
        }, $file, UploadedFile::class)();

        $file->moveTo('tmp/php1');
    }

    public function testMoveToNotValid(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Upload is not valid: test.txt');

        $file = new UploadedFile(
            '/tmp/php1',
            1,
            UPLOAD_ERR_NO_FILE,
            'test.txt',
            'text/plain'
        );

        $file->moveTo('tmp/php1');
    }
}
