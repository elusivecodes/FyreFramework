<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use ErrorException;
use Fyre\Utility\FileSystem\File;
use RuntimeException;

trait CsvTestTrait
{
    public function testCsv(): void
    {
        $file = new File('tmp/test.txt', true);
        $file->open('w+');
        $file->write('1,2,3,4');
        $file->write("\n");
        $file->write('5,6,7,8');
        $file->rewind();

        $this->assertSame(
            ['1', '2', '3', '4'],
            $file->csv()
        );

        $this->assertSame(
            ['5', '6', '7', '8'],
            $file->csv()
        );
    }

    public function testCsvInvalidHandle(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('fgetcsv(): Read of 8192 bytes failed with errno=9 Bad file descriptor');

        $file = new File('tmp/test.txt', true);
        $file->open('w');
        $file->csv();
    }

    public function testCsvNoHandle(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('File handle is not valid.');

        $file = new File('tmp/test.txt', true);
        $file->csv(4);
    }
}
