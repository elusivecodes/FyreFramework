<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Core\Exceptions\ErrorException;
use Fyre\Utility\FileSystem\File;

use function decoct;
use function fileperms;

trait PermissionsTestTrait
{
    public function testPermissions(): void
    {
        $file = new File('tmp/test.txt', true);

        $perms = fileperms('tmp/test.txt');

        $this->assertSame(
            decoct($perms & 0777),
            $file->permissions()
        );
    }

    public function testPermissionsNotExists(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('fileperms(): stat failed for tmp/test.txt');

        $file = new File('tmp/test.txt');
        $file->permissions();
    }
}
