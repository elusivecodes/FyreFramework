<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;

trait FormatTestTrait
{
    public function testFormat(): void
    {
        $this->assertSame(
            'sub/dir/file.ext',
            Path::format(['dirname' => 'sub/dir', 'basename' => 'file.ext'])
        );
    }

    public function testFormatEmptyDirName(): void
    {
        $this->assertSame(
            'file.ext',
            Path::format(['basename' => 'file.ext'])
        );
    }

    public function testFormatEmptyFileName(): void
    {
        $this->assertSame(
            'sub/dir',
            Path::format(['dirname' => 'sub/dir'])
        );
    }
}
