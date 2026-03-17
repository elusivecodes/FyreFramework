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

trait MakeCellTemplateTestTrait
{
    public function testMakeCellTemplate(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:cell_template', ['Example.display'])
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

        $filePath = 'tmp/templates/cells/Example/display.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('cell_template'),
            $filePath
        );
    }

    public function testMakeCellTemplateExistingFile(): void
    {
        $filePath = 'tmp/templates/cells/Example/display.php';
        @mkdir('tmp/templates/cells/Example', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:cell_template', ['Example.display'])
        );

        rewind($this->output);

        $this->assertSame(
            '',
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            Console::style('Cell template file already exists.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );

        $this->assertStringEqualsFile($filePath, 'changed');
    }

    public function testMakeCellTemplateForce(): void
    {
        $filePath = 'tmp/templates/cells/Example/display.php';
        @mkdir('tmp/templates/cells/Example', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:cell_template', [
                'Example.display',
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
            Make::loadStub('cell_template'),
            $filePath
        );
    }

    public function testMakeCellTemplateInvalidPath(): void
    {
        file_put_contents('tmp/invalid', 'invalid');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:cell_template', [
                'Example.display',
                'path' => 'tmp/invalid',
            ])
        );

        rewind($this->output);

        $this->assertSame(
            '',
            stream_get_contents($this->output)
        );

        rewind($this->error);

        $this->assertSame(
            Console::style('Invalid template path.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
    }
}
