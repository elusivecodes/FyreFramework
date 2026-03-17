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

trait MakeTemplateTestTrait
{
    public function testMakeTemplate(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:template', ['Example.index'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $filePath = 'tmp/templates/Example/index.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('template'),
            $filePath
        );
    }

    public function testMakeTemplateExistingFile(): void
    {
        $filePath = 'tmp/templates/Example/index.php';
        @mkdir('tmp/templates/Example', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:template', ['Example.index'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            Console::style('Template file already exists.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertStringEqualsFile($filePath, 'changed');
    }

    public function testMakeTemplateForce(): void
    {
        $filePath = 'tmp/templates/Example/index.php';
        @mkdir('tmp/templates/Example', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:template', [
                'Example.index',
                'force' => true,
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $this->assertFileMatchesFormat(
            Make::loadStub('template'),
            $filePath
        );
    }

    public function testMakeTemplateInvalidPath(): void
    {
        file_put_contents('tmp/invalid', 'invalid');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:template', [
                'Example.index',
                'path' => 'tmp/invalid',
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            Console::style('Invalid template path.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
    }
}
