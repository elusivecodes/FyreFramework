<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Core\Exceptions\ErrorException;
use Fyre\Utility\FileSystem\File;

use function filegroup;

trait GroupTestTrait
{
    public function testGroup(): void
    {
        $file = new File('tmp/test.txt', true);

        $this->assertSame(
            filegroup('tmp/test.txt'),
            $file->group()
        );
    }

    public function testGroupNotExists(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('filegroup(): stat failed for tmp/test.txt');

        $file = new File('tmp/test.txt');
        $file->group();
    }
}
