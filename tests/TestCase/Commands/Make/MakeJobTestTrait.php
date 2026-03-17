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

trait MakeJobTestTrait
{
    public function testMakeJob(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:job', ['Example', 'namespace' => 'Example\Jobs'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $filePath = 'tmp/Jobs/ExampleJob.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('job', [
                '{namespace}' => 'Example\Jobs',
                '{class}' => 'ExampleJob',
            ]),
            $filePath
        );
    }

    public function testMakeJobExistingFile(): void
    {
        $filePath = 'tmp/Jobs/ExampleJob.php';
        @mkdir('tmp/Jobs', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:job', ['Example', 'namespace' => 'Example\Jobs'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            Console::style('Job file already exists.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertStringEqualsFile($filePath, 'changed');
    }

    public function testMakeJobForce(): void
    {
        $filePath = 'tmp/Jobs/ExampleJob.php';
        @mkdir('tmp/Jobs', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:job', [
                'Example',
                'namespace' => 'Example\Jobs',
                'force' => true,
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $this->assertFileMatchesFormat(
            Make::loadStub('job', [
                '{namespace}' => 'Example\Jobs',
                '{class}' => 'ExampleJob',
            ]),
            $filePath
        );
    }

    public function testMakeJobNamespaceNotFound(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:job', [
                'Example',
                'namespace' => 'Missing\Jobs',
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            Console::style('Namespace path not found.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertFileDoesNotExist('tmp/Jobs/ExampleJob.php');
    }
}
