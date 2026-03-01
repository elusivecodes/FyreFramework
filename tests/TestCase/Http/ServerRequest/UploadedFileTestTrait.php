<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\ServerRequest;
use Fyre\Http\UploadedFile;

trait UploadedFileTestTrait
{
    public function testGetUploadedFile(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'files' => [
                'test' => [
                    'tmp_name' => '/tmp/tempname',
                    'name' => 'test.txt',
                    'type' => 'text/plain',
                    'size' => 1,
                    'error' => 0,
                ],
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
        $request = new ServerRequest($this->config, $this->type, [
            'files' => [
                'test' => [
                    'tmp_name' => '/tmp/tempname',
                    'name' => 'test.txt',
                    'type' => 'text/plain',
                    'size' => 1,
                    'error' => 0,
                ],
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
        $request = new ServerRequest($this->config, $this->type, [
            'files' => [
                'test' => [
                    'tmp_name' => [
                        '/tmp/tempname1',
                        '/tmp/tempname2',
                    ],
                    'name' => [
                        'test1.txt',
                        'test2.txt',
                    ],
                    'type' => [
                        'text/plain',
                        'text/plain',
                    ],
                    'size' => [
                        1,
                        1,
                    ],
                    'error' => [
                        0,
                        0,
                    ],
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
        $request = new ServerRequest($this->config, $this->type, [
            'files' => [
                'test' => [
                    'tmp_name' => [
                        'a' => '/tmp/tempname',
                    ],
                    'name' => [
                        'a' => 'test.txt',
                    ],
                    'type' => [
                        'a' => 'text/plain',
                    ],
                    'size' => [
                        'a' => 1,
                    ],
                    'error' => [
                        'a' => 0,
                    ],
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

        $this->assertSame(
            [
                'test' => $file,
            ],
            $request2->getUploadedFiles()
        );
    }
}
