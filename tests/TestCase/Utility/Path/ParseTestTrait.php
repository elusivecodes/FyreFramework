<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;

trait ParseTestTrait
{
    public function testParseWithDeepPath(): void
    {
        $this->assertArraysAreIdentical(
            [
                'dirname' => 'sub/dir',
                'basename' => 'file.ext',
                'extension' => 'ext',
                'filename' => 'file',
            ],
            Path::parse('sub/dir/file.ext')
        );
    }

    public function testParseWithEmptyString(): void
    {
        $this->assertArraysAreIdentical(
            [
                'basename' => '',
                'filename' => '',
            ],
            Path::parse('')
        );
    }

    public function testParseWithFileName(): void
    {
        $this->assertArraysAreIdentical(
            [
                'dirname' => '.',
                'basename' => 'file.ext',
                'extension' => 'ext',
                'filename' => 'file',
            ],
            Path::parse('file.ext')
        );
    }

    public function testParseWithFullPath(): void
    {
        $this->assertArraysAreIdentical(
            [
                'dirname' => '/sub/dir',
                'basename' => 'file.ext',
                'extension' => 'ext',
                'filename' => 'file',
            ],
            Path::parse('/sub/dir/file.ext')
        );
    }

    public function testParseWithMultipleExtensions(): void
    {
        $this->assertArraysAreIdentical(
            [
                'dirname' => 'dir',
                'basename' => 'file.tst.ext',
                'extension' => 'ext',
                'filename' => 'file.tst',
            ],
            Path::parse('dir/file.tst.ext')
        );
    }

    public function testParseWithNoExtension(): void
    {
        $this->assertArraysAreIdentical(
            [
                'dirname' => 'dir',
                'basename' => 'file',
                'filename' => 'file',
            ],
            Path::parse('dir/file')
        );
    }

    public function testParseWithPath(): void
    {
        $this->assertArraysAreIdentical(
            [
                'dirname' => 'dir',
                'basename' => 'file.ext',
                'extension' => 'ext',
                'filename' => 'file',
            ],
            Path::parse('dir/file.ext')
        );
    }
}
