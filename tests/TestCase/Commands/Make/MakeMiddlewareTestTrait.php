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

trait MakeMiddlewareTestTrait
{
    public function testMakeMiddleware(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:middleware', ['Example', 'namespace' => 'Example\Middleware'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $filePath = 'tmp/Middleware/ExampleMiddleware.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('middleware', [
                '{namespace}' => 'Example\Middleware',
                '{class}' => 'ExampleMiddleware',
            ]),
            $filePath
        );
    }

    public function testMakeMiddlewareExistingFile(): void
    {
        $filePath = 'tmp/Middleware/ExampleMiddleware.php';
        @mkdir('tmp/Middleware', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:middleware', ['Example', 'namespace' => 'Example\Middleware'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            Console::style('Middleware file already exists.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertStringEqualsFile($filePath, 'changed');
    }

    public function testMakeMiddlewareForce(): void
    {
        $filePath = 'tmp/Middleware/ExampleMiddleware.php';
        @mkdir('tmp/Middleware', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:middleware', [
                'Example',
                'namespace' => 'Example\Middleware',
                'force' => true,
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $this->assertFileMatchesFormat(
            Make::loadStub('middleware', [
                '{namespace}' => 'Example\Middleware',
                '{class}' => 'ExampleMiddleware',
            ]),
            $filePath
        );
    }

    public function testMakeMiddlewareNamespaceNotFound(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:middleware', [
                'Example',
                'namespace' => 'Missing\Middleware',
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            Console::style('Namespace path not found.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertFileDoesNotExist('tmp/Middleware/ExampleMiddleware.php');
    }
}
