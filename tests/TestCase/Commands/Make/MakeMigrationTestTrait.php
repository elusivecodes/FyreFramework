<?php
declare(strict_types=1);

namespace Tests\TestCase\Commands\Make;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Core\Make;

use function file_put_contents;
use function glob;
use function mkdir;
use function pathinfo;
use function rewind;
use function stream_get_contents;

trait MakeMigrationTestTrait
{
    public function testMakeMigration(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:migration', ['CreateTables', '20240101'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $filePath = 'tmp/Migrations/Migration_20240101_CreateTables.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('migration', [
                '{namespace}' => 'Example\Migrations',
                '{class}' => 'Migration_20240101_CreateTables',
            ]),
            $filePath
        );
    }

    public function testMakeMigrationDefaultVersion(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:migration', ['CreateTables'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $files = glob('tmp/Migrations/Migration_*_CreateTables.php');

        $this->assertIsArray($files);
        $this->assertCount(1, $files);

        $filePath = $files[0];
        $className = pathinfo($filePath, PATHINFO_FILENAME);

        $this->assertFileMatchesFormat(
            Make::loadStub('migration', [
                '{namespace}' => 'Example\Migrations',
                '{class}' => $className,
            ]),
            $filePath
        );
    }

    public function testMakeMigrationExistingFile(): void
    {
        $filePath = 'tmp/Migrations/Migration_20240101_CreateTables.php';
        @mkdir('tmp/Migrations', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:migration', ['CreateTables', '20240101'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            Console::style('Migration file already exists.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertStringEqualsFile($filePath, 'changed');
    }

    public function testMakeMigrationForce(): void
    {
        $filePath = 'tmp/Migrations/Migration_20240101_CreateTables.php';
        @mkdir('tmp/Migrations', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:migration', [
                'CreateTables',
                '20240101',
                'force' => true,
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $this->assertFileMatchesFormat(
            Make::loadStub('migration', [
                '{namespace}' => 'Example\Migrations',
                '{class}' => 'Migration_20240101_CreateTables',
            ]),
            $filePath
        );
    }

    public function testMakeMigrationNamespaceNotFound(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:migration', [
                'CreateTables',
                '20240101',
                'namespace' => 'Missing\Migrations',
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            Console::style('Namespace path not found.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertFileDoesNotExist('tmp/Migrations/Migration_20240101_CreateTables.php');
    }
}
