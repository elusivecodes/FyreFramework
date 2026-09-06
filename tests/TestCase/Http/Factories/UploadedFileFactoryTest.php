<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Factories;

use Fyre\Http\Factories\UploadedFileFactory;
use Fyre\Http\Stream;
use Fyre\Http\UploadedFile;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

use function sys_get_temp_dir;
use function tempnam;
use function unlink;

use const UPLOAD_ERR_OK;

final class UploadedFileFactoryTest extends TestCase
{
    protected UploadedFileFactory $uploadedFileFactory;

    public function testCreateUploadedFile(): void
    {
        $stream = Stream::createFromString('This is a test.');
        $file = $this->uploadedFileFactory->createUploadedFile(
            $stream,
            null,
            UPLOAD_ERR_OK,
            'test.txt',
            'text/plain'
        );

        $this->assertInstanceOf(
            UploadedFile::class,
            $file
        );

        $this->assertSame(
            $stream,
            $file->getStream()
        );

        $this->assertSame(
            15,
            $file->getSize()
        );

        $this->assertSame(
            UPLOAD_ERR_OK,
            $file->getError()
        );

        $this->assertSame(
            'test.txt',
            $file->getClientFilename()
        );

        $this->assertSame(
            'text/plain',
            $file->getClientMediaType()
        );
    }

    public function testCreateUploadedFileNotReadable(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Uploaded file stream must be readable.');

        $filePath = tempnam(sys_get_temp_dir(), 'uploaded-file-factory');

        $this->assertIsString($filePath);

        $stream = Stream::createFromFile($filePath, 'w');

        try {
            $this->uploadedFileFactory->createUploadedFile($stream);
        } finally {
            $stream->close();
            @unlink($filePath);
        }
    }

    public function testCreateUploadedFiles(): void
    {
        $files = $this->uploadedFileFactory->createUploadedFiles([
            'documents' => [
                'error' => [
                    UPLOAD_ERR_OK,
                    UPLOAD_ERR_OK,
                ],
                'name' => [
                    'first.txt',
                    'second.txt',
                ],
                'size' => [
                    5,
                    6,
                ],
                'tmp_name' => [
                    '/tmp/first',
                    '/tmp/second',
                ],
                'type' => [
                    'text/plain',
                    'text/plain',
                ],
            ],
        ]);

        $documents = $files['documents'];

        $this->assertIsArray($documents);

        $this->assertContainsOnlyInstancesOf(
            UploadedFileInterface::class,
            $documents
        );

        $this->assertSame(
            'first.txt',
            $documents[0]->getClientFilename()
        );

        $this->assertSame(
            'second.txt',
            $documents[1]->getClientFilename()
        );
    }

    public function testCreateUploadedFilesInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Uploaded file data is not valid.');

        $this->uploadedFileFactory->createUploadedFiles([
            'document' => 'invalid',
        ]);
    }

    public function testCreateUploadedFilesInvalidArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Uploaded file data is not valid.');

        $this->uploadedFileFactory->createUploadedFiles([
            'document' => [
                'error' => UPLOAD_ERR_OK,
                'name' => ['document.txt'],
                'size' => [4],
                'tmp_name' => ['/tmp/document'],
                'type' => ['text/plain'],
            ],
        ]);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->uploadedFileFactory = new UploadedFileFactory();
    }
}
