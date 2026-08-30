<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\ServerRequest;
use Fyre\Http\UploadedFile;
use InvalidArgumentException;

trait UploadedFileTestTrait
{
    public function testGetUploadedFile(): void
    {
        $file = new UploadedFile(
            '/tmp/tempname',
            1,
            0,
            'test.txt',
            'text/plain'
        );

        $request = new ServerRequest($this->config, $this->type, [
            'files' => [
                'test' => $file,
            ],
        ]);

        $file = $request->getUploadedFile('test');

        $this->assertInstanceOf(
            UploadedFile::class,
            $file
        );

        $this->assertSame(
            'test.txt',
            $file->getClientFilename()
        );

        $this->assertSame(
            'text/plain',
            $file->getClientMediaType()
        );

        $this->assertSame(
            0,
            $file->getError()
        );
    }

    public function testGetUploadedFileAll(): void
    {
        $file = new UploadedFile(
            '/tmp/tempname',
            1,
            0,
            'test.txt',
            'text/plain'
        );

        $request = new ServerRequest($this->config, $this->type, [
            'files' => [
                'test' => $file,
            ],
        ]);

        $files = $request->getUploadedFile();

        $this->assertArrayHasKey(
            'test',
            $files
        );

        $this->assertInstanceOf(
            UploadedFile::class,
            $files['test']
        );
    }

    public function testGetUploadedFileArray(): void
    {
        $file1 = new UploadedFile(
            '/tmp/tempname1',
            1,
            0,
            'test1.txt',
            'text/plain'
        );
        $file2 = new UploadedFile(
            '/tmp/tempname2',
            1,
            0,
            'test2.txt',
            'text/plain'
        );

        $request = new ServerRequest($this->config, $this->type, [
            'files' => [
                'test' => [
                    $file1,
                    $file2,
                ],
            ],
        ]);

        $files = $request->getUploadedFile('test');

        $this->assertCount(
            2,
            $files
        );

        $this->assertInstanceOf(
            UploadedFile::class,
            $files[0]
        );

        $this->assertInstanceOf(
            UploadedFile::class,
            $files[1]
        );
    }

    public function testGetUploadedFileDeep(): void
    {
        $file = new UploadedFile(
            '/tmp/tempname',
            1,
            0,
            'test.txt',
            'text/plain'
        );

        $request = new ServerRequest($this->config, $this->type, [
            'files' => [
                'test' => [
                    'a' => $file,
                ],
            ],
        ]);

        $file = $request->getUploadedFile('test.a');

        $this->assertInstanceOf(
            UploadedFile::class,
            $file
        );

        $this->assertSame(
            'test.txt',
            $file->getClientFilename()
        );

        $this->assertSame(
            'text/plain',
            $file->getClientMediaType()
        );

        $this->assertSame(
            0,
            $file->getError()
        );
    }

    public function testGetUploadedFileInvalid(): void
    {
        $request = new ServerRequest($this->config, $this->type);

        $this->assertNull(
            $request->getUploadedFile('invalid')
        );
    }

    public function testWithUploadedFiles(): void
    {
        $file = new UploadedFile(
            '/tmp/tempname',
            1,
            0,
            'test.txt',
            'text/plain',
        );

        $request1 = new ServerRequest($this->config, $this->type);
        $request2 = $request1->withUploadedFiles(['test' => $file]);

        $this->assertEmpty(
            $request1->getUploadedFiles()
        );

        $this->assertArraysAreIdentical(
            [
                'test' => $file,
            ],
            $request2->getUploadedFiles()
        );
    }

    public function testWithUploadedFilesInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Uploaded file `test.nested` is not valid.');

        $request = new ServerRequest($this->config, $this->type);

        $request->withUploadedFiles([
            'test' => [
                'nested' => 'invalid',
            ],
        ]);
    }
}
