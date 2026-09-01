<?php
declare(strict_types=1);

namespace Tests\TestCase\Core\Make;

use Fyre\Core\Make\GeneratedFile;
use Fyre\Core\Make\GenerationBatch;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

use function file_put_contents;
use function glob;
use function mkdir;
use function rmdir;
use function unlink;

final class GenerationBatchTest extends TestCase
{
    protected string $path = 'tmp/GenerationBatch';

    public function testDuplicateDestination(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Generated file destination collision: `example.php`.');

        $batch = new GenerationBatch(new GeneratedFile('example.php', 'first'));

        $batch->addFile(new GeneratedFile('example.php', 'second'));
    }

    public function testPaths(): void
    {
        $batch = new GenerationBatch(
            new GeneratedFile('first.php', 'first'),
            new GeneratedFile('nested/second.php', 'second')
        );

        $this->assertSame(
            [
                'first.php',
                'nested/second.php',
            ],
            $batch->paths()
        );
    }

    public function testPreflight(): void
    {
        $first = $this->path.'/first.php';
        $second = $this->path.'/second.php';
        file_put_contents($second, 'second before');

        $batch = new GenerationBatch(
            new GeneratedFile($first, 'first'),
            new GeneratedFile($second, 'second')
        );

        $this->assertFalse($batch->save());
        $this->assertFileDoesNotExist($first);
        $this->assertStringEqualsFile($second, 'second before');
    }

    public function testSave(): void
    {
        $first = $this->path.'/first.php';
        $second = $this->path.'/nested/second.php';
        $batch = new GenerationBatch(
            new GeneratedFile($first, 'first'),
            new GeneratedFile($second, 'second')
        );

        $this->assertTrue($batch->save());
        $this->assertStringEqualsFile($first, 'first');
        $this->assertStringEqualsFile($second, 'second');
    }

    #[Override]
    protected function setUp(): void
    {
        @mkdir($this->path, 0755, true);
    }

    #[Override]
    protected function tearDown(): void
    {
        foreach (glob($this->path.'/nested/*') ?: [] as $file) {
            @unlink($file);
        }

        @rmdir($this->path.'/nested');

        foreach (glob($this->path.'/*') ?: [] as $file) {
            @unlink($file);
        }

        @rmdir($this->path);
    }
}
