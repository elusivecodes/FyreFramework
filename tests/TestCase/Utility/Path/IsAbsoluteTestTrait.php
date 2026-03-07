<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Utility\Path;

trait IsAbsoluteTestTrait
{
    public function testIsAbsolute(): void
    {
        $this->assertTrue(
            Path::isAbsolute('/path/to/file')
        );
    }

    public function testIsAbsoluteWithRelative(): void
    {
        $this->assertFalse(
            Path::isAbsolute('path/to/file')
        );
    }

    public function testIsAbsoluteWithWindowsDriveLetter(): void
    {
        $this->assertTrue(
            Path::isAbsolute('C:\path\to\file')
        );
    }

    public function testIsAbsoluteWithWindowsRelativeDrivePath(): void
    {
        $this->assertFalse(
            Path::isAbsolute('C:path\to\file')
        );
    }
}
