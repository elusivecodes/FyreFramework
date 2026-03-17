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

trait MakeCommandCommandTestTrait
{
    public function testMakeCommand(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:command', ['Example'])
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

        $filePath = 'tmp/Commands/ExampleCommand.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('command', [
                '{namespace}' => 'Example\Commands',
                '{class}' => 'ExampleCommand',
                '{alias}' => 'example',
                '{name}' => 'Example',
                '{description}' => '',
            ]),
            $filePath
        );
    }

    public function testMakeCommandExistingFile(): void
    {
        $filePath = 'tmp/Commands/ExampleCommand.php';
        @mkdir('tmp/Commands', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:command', ['Example'])
        );

        rewind($this->output);

        $this->assertSame(
            '',
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            Console::style('Command file already exists.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );

        $this->assertStringEqualsFile($filePath, 'changed');
    }

    public function testMakeCommandForce(): void
    {
        $filePath = 'tmp/Commands/ExampleCommand.php';
        @mkdir('tmp/Commands', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:command', [
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
            Make::loadStub('command', [
                '{namespace}' => 'Example\Commands',
                '{class}' => 'ExampleCommand',
                '{alias}' => 'example',
                '{description}' => '',
            ]),
            $filePath
        );
    }

    public function testMakeCommandNamespaceNotFound(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:command', [
                'Example',
                'namespace' => 'Missing\Commands',
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

        $this->assertFileDoesNotExist('tmp/Commands/ExampleCommand.php');
    }
}
