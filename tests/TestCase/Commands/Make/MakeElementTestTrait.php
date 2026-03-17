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

trait MakeElementTestTrait
{
    public function testMakeElement(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:element', ['example'])
        );

        rewind($this->output);

        $this->assertSame('', stream_get_contents($this->output));

        rewind($this->error);

        $this->assertSame('', stream_get_contents($this->error));

        $filePath = 'tmp/templates/elements/example.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('element'),
            $filePath
        );
    }

    public function testMakeElementExistingFile(): void
    {
        $filePath = 'tmp/templates/elements/example.php';
        @mkdir('tmp/templates/elements', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:element', ['example'])
        );

        rewind($this->output);

        $this->assertSame('', stream_get_contents($this->output));

        rewind($this->error);

        $this->assertSame(
            Console::style('Element file already exists.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );

        $this->assertStringEqualsFile($filePath, 'changed');
    }

    public function testMakeElementForce(): void
    {
        $filePath = 'tmp/templates/elements/example.php';
        @mkdir('tmp/templates/elements', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:element', [
                'example',
                'force' => true,
            ])
        );

        rewind($this->output);

        $this->assertSame('', stream_get_contents($this->output));

        rewind($this->error);

        $this->assertSame('', stream_get_contents($this->error));

        $this->assertFileMatchesFormat(
            Make::loadStub('element'),
            $filePath
        );
    }

    public function testMakeElementInvalidPath(): void
    {
        file_put_contents('tmp/invalid', 'invalid');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:element', [
                'example',
                'path' => 'tmp/invalid',
            ])
        );

        rewind($this->output);

        $this->assertSame('', stream_get_contents($this->output));

        rewind($this->error);

        $this->assertSame(
            Console::style('Invalid element path.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
    }
}
