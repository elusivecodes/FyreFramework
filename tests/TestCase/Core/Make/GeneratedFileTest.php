<?php
declare(strict_types=1);

namespace Tests\TestCase\Core\Make;

use Fyre\Core\Make\GeneratedFile;
use Override;
use PHPUnit\Framework\TestCase;

use function file_put_contents;
use function mkdir;
use function rmdir;
use function unlink;

final class GeneratedFileTest extends TestCase
{
    protected string $path = 'tmp/GeneratedFile';

    public function testIsValid(): void
    {
        $path = $this->path.'/example.php';
        $generatedFile = new GeneratedFile($path, 'contents');

        $this->assertTrue($generatedFile->isValid());
        file_put_contents($path, 'contents');
        $this->assertFalse($generatedFile->isValid());
        $this->assertTrue($generatedFile->isValid(true));
        $this->assertFalse(new GeneratedFile($this->path, 'contents')->isValid(true));
    }

    public function testSave(): void
    {
        $path = $this->path.'/nested/example.php';
        $generatedFile = new GeneratedFile($path, 'contents');

        $this->assertTrue($generatedFile->save());
        $this->assertStringEqualsFile($path, 'contents');
    }

    #[Override]
    protected function setUp(): void
    {
        @mkdir($this->path, 0755, true);
    }

    #[Override]
    protected function tearDown(): void
    {
        @unlink($this->path.'/nested/example.php');
        @rmdir($this->path.'/nested');
        @unlink($this->path.'/example.php');
        @rmdir($this->path);
    }
}
