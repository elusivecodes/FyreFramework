<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Core\Exceptions\ErrorException;
use Fyre\Utility\FileSystem\File;

use function fileowner;

trait OwnerTestTrait
{
    public function testOwner(): void
    {
        $file = new File('tmp/test.txt', true);

        $this->assertSame(
            fileowner('tmp/test.txt'),
            $file->owner()
        );
    }

    public function testOwnerNotExists(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('fileowner(): stat failed for tmp/test.txt');

        $file = new File('tmp/test.txt');
        $file->owner();
    }
}
