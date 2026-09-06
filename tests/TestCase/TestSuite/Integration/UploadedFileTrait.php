<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use InvalidArgumentException;

use function file_get_contents;
use function file_put_contents;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

trait UploadedFileTrait
{
    public function testUploadedFile(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'uploaded-file');

        $this->assertIsString($filePath);

        file_put_contents($filePath, 'This is a test.');

        try {
            $this->enableCsrfToken();
            $this->file(
                'profile.avatar',
                $filePath,
                'test.txt',
                'text/plain'
            );
            $this->post('/upload', ['value' => 1]);

            $this->assertResponseEquals(
                '{"contentType":"multipart\/form-data",'.
                '"filename":"test.txt","mediaType":"text\/plain",'.
                '"contents":"This is a test.","data":{"value":"1"}}'
            );

            $this->post('/upload');

            $this->assertResponseEquals('No file.');
        } finally {
            @unlink($filePath);
        }
    }

    public function testUploadedFileInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Uploaded file `invalid` is not valid.');

        $this->file('avatar', 'invalid');
    }

    public function testUploadedFileMove(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'uploaded-file');

        $this->assertIsString($filePath);

        $targetPath = $filePath.'.moved';
        file_put_contents($filePath, 'This is a test.');

        try {
            $this->enableCsrfToken();
            $this->file('profile.avatar', $filePath);
            $this->post('/upload/move', ['target' => $targetPath]);

            $this->assertResponseEmpty();
            $this->assertFileExists($filePath);
            $this->assertSame(
                'This is a test.',
                file_get_contents($targetPath)
            );
        } finally {
            @unlink($filePath);
            @unlink($targetPath);
        }
    }
}
