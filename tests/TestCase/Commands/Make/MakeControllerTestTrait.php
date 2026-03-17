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

trait MakeControllerTestTrait
{
    public function testMakeController(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:controller', ['Example', 'namespace' => 'Example\Controllers'])
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

        $filePath = 'tmp/Controllers/ExampleController.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('controller', [
                '{namespace}' => 'Example\Controllers',
                '{class}' => 'ExampleController',
            ]),
            $filePath
        );
    }

    public function testMakeControllerExistingFile(): void
    {
        $filePath = 'tmp/Controllers/ExampleController.php';
        @mkdir('tmp/Controllers', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:controller', ['Example', 'namespace' => 'Example\Controllers'])
        );

        rewind($this->output);

        $this->assertSame(
            '',
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            Console::style('Controller file already exists.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );

        $this->assertStringEqualsFile($filePath, 'changed');
    }

    public function testMakeControllerForce(): void
    {
        $filePath = 'tmp/Controllers/ExampleController.php';
        @mkdir('tmp/Controllers', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:controller', [
                'Example',
                'namespace' => 'Example\Controllers',
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
            Make::loadStub('controller', [
                '{namespace}' => 'Example\Controllers',
                '{class}' => 'ExampleController',
            ]),
            $filePath
        );
    }

    public function testMakeControllerNamespaceNotFound(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:controller', [
                'Example',
                'namespace' => 'Missing\Controllers',
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

        $this->assertFileDoesNotExist('tmp/Controllers/ExampleController.php');
    }
}
