<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Core\Exceptions\ErrorException;
use Fyre\Utility\FileSystem\File;

trait ChmodTestTrait
{
    public function testChmod(): void
    {
        $file = new File('tmp/test.txt', true);

        $this->assertSame(
            $file,
            $file->chmod(777)
        );
    }

    public function testChmodNotExists(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('chmod(): No such file or directory');

        $file = new File('tmp/test.txt');
        $file->chmod(777);
    }
}
