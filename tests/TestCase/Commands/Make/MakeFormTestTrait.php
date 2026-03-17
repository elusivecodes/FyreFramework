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

trait MakeFormTestTrait
{
    public function testMakeForm(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:form', ['Example', 'namespace' => 'Example\Forms'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $filePath = 'tmp/Forms/ExampleForm.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('form', [
                '{namespace}' => 'Example\Forms',
                '{class}' => 'ExampleForm',
            ]),
            $filePath
        );
    }

    public function testMakeFormExistingFile(): void
    {
        $filePath = 'tmp/Forms/ExampleForm.php';
        @mkdir('tmp/Forms', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:form', ['Example', 'namespace' => 'Example\Forms'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            Console::style('Form file already exists.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertStringEqualsFile($filePath, 'changed');
    }

    public function testMakeFormForce(): void
    {
        $filePath = 'tmp/Forms/ExampleForm.php';
        @mkdir('tmp/Forms', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:form', [
                'Example',
                'namespace' => 'Example\Forms',
                'force' => true,
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $this->assertFileMatchesFormat(
            Make::loadStub('form', [
                '{namespace}' => 'Example\Forms',
                '{class}' => 'ExampleForm',
            ]),
            $filePath
        );
    }

    public function testMakeFormNamespaceNotFound(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:form', [
                'Example',
                'namespace' => 'Missing\Forms',
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            Console::style('Namespace path not found.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertFileDoesNotExist('tmp/Forms/ExampleForm.php');
    }
}
