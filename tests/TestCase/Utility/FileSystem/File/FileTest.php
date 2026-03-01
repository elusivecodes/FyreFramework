<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\FileSystem\File;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\FileSystem\File;
use Fyre\Utility\FileSystem\Folder;
use Fyre\Utility\Path;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;
use function mkdir;
use function rmdir;
use function unlink;

final class FileTest extends TestCase
{
    use AccessTimeTestTrait;
    use ChmodTestTrait;
    use CloseTestTrait;
    use ContentsTestTrait;
    use CopyTestTrait;
    use CreateTestTrait;
    use CsvTestTrait;
    use DeleteTestTrait;
    use EndedTestTrait;
    use GroupTestTrait;
    use LockTestTrait;
    use MimeTypeTestTrait;
    use ModifiedTimeTestTrait;
    use OwnerTestTrait;
    use PermissionsTestTrait;
    use ReadTestTrait;
    use RewindTestTrait;
    use SeekTestTrait;
    use SizeTestTrait;
    use TruncateTestTrait;
    use WriteTestTrait;

    public function testBaseName(): void
    {
        $file = new File('tmp/test/test.txt');

        $this->assertSame(
            'test.txt',
            $file->baseName()
        );
    }

    public function testCreateNew(): void
    {
        $file = new File('tmp/test.txt', true);

        $this->assertTrue(
            $file->exists()
        );
    }

    public function testCreateNewDeep(): void
    {
        $file = new File('tmp/test/test.txt', true);

        $this->assertTrue(
            $file->exists()
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(File::class)
        );
    }

    public function testDirName(): void
    {
        $file = new File('tmp/test/test.txt');

        $this->assertSame(
            Path::resolve('tmp/test'),
            $file->dirName()
        );
    }

    public function testExtension(): void
    {
        $file = new File('tmp/test/test.txt');

        $this->assertSame(
            'txt',
            $file->extension()
        );
    }

    public function testFile(): void
    {
        $file = new File('tmp/test.txt');

        $this->assertFalse(
            $file->exists()
        );
    }

    public function testFileName(): void
    {
        $file = new File('tmp/test/test.txt');

        $this->assertSame(
            'test',
            $file->fileName()
        );
    }

    public function testFolder(): void
    {
        $file = new File('tmp/test/test.txt');

        $this->assertInstanceOf(
            Folder::class,
            $file->folder()
        );

        $this->assertSame(
            Path::resolve('tmp/test'),
            $file->folder()->path()
        );
    }

    public function testIsExecutable(): void
    {
        $file = new File('tmp/test/test.txt', true);

        $this->assertFalse(
            $file->isExecutable()
        );
    }

    public function testIsReadable(): void
    {
        $file = new File('tmp/test/test.txt', true);

        $this->assertTrue(
            $file->isReadable()
        );
    }

    public function testIsWritable(): void
    {
        $file = new File('tmp/test/test.txt', true);

        $this->assertTrue(
            $file->isWritable()
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(File::class)
        );
    }

    public function testPath(): void
    {
        $file = new File('tmp/test/test.txt');

        $this->assertSame(
            Path::resolve('tmp/test/test.txt'),
            $file->path()
        );
    }

    public function testPathDots(): void
    {
        $file = new File('tmp/test/../test.txt');

        $this->assertSame(
            Path::resolve('tmp/test.txt'),
            $file->path()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        @mkdir('tmp');
    }

    #[Override]
    protected function tearDown(): void
    {
        @unlink('tmp/test.txt');
        @unlink('tmp/test2.txt');
        @unlink('tmp/test/test.txt');
        @rmdir('tmp/test');
        @rmdir('tmp');
    }
}
