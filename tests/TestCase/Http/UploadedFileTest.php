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
use function file_get_contents;
use function file_put_contents;
use function sys_get_temp_dir;
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

    public function testGetStreamMoved(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Upload already moved: test.txt');

        $filePath = tempnam(sys_get_temp_dir(), 'uploaded-file');

        assert($filePath !== false);

        $targetPath = $filePath.'.moved';
        file_put_contents($filePath, 'This is a test.');

        $file = new UploadedFile(
            $filePath,
            15,
            UPLOAD_ERR_OK,
            'test.txt',
            'text/plain'
        );

        try {
            $file->moveTo($targetPath);
            $file->getStream();
        } finally {
            @unlink($filePath);
            @unlink($targetPath);
        }
    }

    public function testMoveTo(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Failed to move upload `test.txt` to `tmp/php1`.');

        $file = new UploadedFile(
            '/tmp/php1',
            1,
            UPLOAD_ERR_OK,
            'test.txt',
            'text/plain'
        );

        $file->moveTo('tmp/php1');
    }

    public function testMoveToFile(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'uploaded-file');

        assert($filePath !== false);

        $targetPath = $filePath.'.moved';
        file_put_contents($filePath, 'This is a test.');

        $file = new UploadedFile(
            $filePath,
            15,
            UPLOAD_ERR_OK,
            'test.txt',
            'text/plain'
        );

        try {
            $file->getStream();
            $file->moveTo($targetPath);

            $this->assertFileDoesNotExist($filePath);
            $this->assertSame(
                'This is a test.',
                file_get_contents($targetPath)
            );
        } finally {
            @unlink($filePath);
            @unlink($targetPath);
        }
    }

    public function testMoveToMoved(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Upload already moved: test.txt');

        $file = new UploadedFile(
            '/tmp/php1',
            1,
            UPLOAD_ERR_OK,
            'test.txt',
            'text/plain'
        );

        Closure::bind(function(): void {
            /** @var UploadedFile $this */
            $this->hasMoved = true;
        }, $file, UploadedFile::class)();

        $file->moveTo('tmp/php1');
    }

    public function testMoveToNotValid(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Upload is not valid: test.txt');

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
