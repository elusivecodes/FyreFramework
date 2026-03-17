<?php
declare(strict_types=1);

namespace Tests\TestCase\Commands\Make;

use Fyre\Console\Command;
use Fyre\Console\Console;
use Fyre\Core\Make;

use function file_put_contents;
use function mkdir;
use function rewind;
use function stream_get_contents;

trait MakeCellTestTrait
{
    public function testMakeCell(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:cell', ['Example'])
        );

        rewind($this->output);

        $this->assertSame(
            '',
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            '',
            stream_get_contents($this->error)
        );

        $filePath = 'tmp/Cells/ExampleCell.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('cell', [
                '{namespace}' => 'Example\Cells',
                '{class}' => 'ExampleCell',
                '{method}' => 'display',
            ]),
            $filePath
        );
    }

    public function testMakeCellExistingFile(): void
    {
        $filePath = 'tmp/Cells/ExampleCell.php';
        @mkdir('tmp/Cells', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:cell', ['Example'])
        );

        rewind($this->output);

        $this->assertSame(
            '',
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            Console::style('Cell file already exists.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );

        $this->assertStringEqualsFile($filePath, 'changed');
    }

    public function testMakeCellForce(): void
    {
        $filePath = 'tmp/Cells/ExampleCell.php';
        @mkdir('tmp/Cells', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:cell', [
                'Example',
                'force' => true,
            ])
        );

        rewind($this->output);

        $this->assertSame(
            '',
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            '',
            stream_get_contents($this->error)
        );

        $this->assertFileMatchesFormat(
            Make::loadStub('cell', [
                '{namespace}' => 'Example\Cells',
                '{class}' => 'ExampleCell',
                '{method}' => 'display',
            ]),
            $filePath
        );
    }

    public function testMakeCellNamespaceNotFound(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:cell', [
                'Example',
                'namespace' => 'Missing\Cells',
            ])
        );

        rewind($this->output);

        $this->assertSame(
            '',
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            Console::style('Namespace path not found.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );

        $this->assertFileDoesNotExist('tmp/Cells/ExampleCell.php');
    }
}
