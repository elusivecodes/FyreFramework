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

use const PHP_EOL;

trait MakeEnumTestTrait
{
    public function testMakeEnum(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:enum', [
                'Status',
                'cases' => 'Draft:draft,Published:published',
                'namespace' => 'Example\Enums',
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $filePath = 'tmp/Enums/Status.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('enum', [
                '{namespace}' => 'Example\Enums',
                '{class}' => 'Status',
                '{type}' => ': string',
                '{cases}' => '    case Draft = \'draft\';'.PHP_EOL.
                    '    case Published = \'published\';',
            ]),
            $filePath
        );
    }

    public function testMakeEnumExistingFile(): void
    {
        $filePath = 'tmp/Enums/Status.php';
        @mkdir('tmp/Enums', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:enum', [
                'Status',
                'namespace' => 'Example\Enums',
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            Console::style('Enum file already exists.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertStringEqualsFile($filePath, 'changed');
    }

    public function testMakeEnumForce(): void
    {
        $filePath = 'tmp/Enums/Status.php';
        @mkdir('tmp/Enums', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:enum', [
                'Status',
                'cases' => 'Draft',
                'namespace' => 'Example\Enums',
                'force' => true,
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $this->assertFileMatchesFormat(
            Make::loadStub('enum', [
                '{namespace}' => 'Example\Enums',
                '{class}' => 'Status',
                '{type}' => '',
                '{cases}' => '    case Draft;',
            ]),
            $filePath
        );
    }

    public function testMakeEnumInvalidCase(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:enum', [
                'Status',
                'cases' => 'Draft,invalid-case',
                'namespace' => 'Example\Enums',
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            Console::style('Invalid enum case.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertFileDoesNotExist('tmp/Enums/Status.php');
    }

    public function testMakeEnumNamespaceNotFound(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:enum', [
                'Status',
                'namespace' => 'Missing\Enums',
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            Console::style('Namespace path not found.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertFileDoesNotExist('tmp/Enums/Status.php');
    }

    public function testMakeEnumStringDefaultValue(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:enum', [
                'Status',
                'cases' => 'Draft,Published:published',
                'namespace' => 'Example\Enums',
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $filePath = 'tmp/Enums/Status.php';

        $this->assertFileMatchesFormat(
            Make::loadStub('enum', [
                '{namespace}' => 'Example\Enums',
                '{class}' => 'Status',
                '{type}' => ': string',
                '{cases}' => '    case Draft = \'draft\';'.PHP_EOL.
                    '    case Published = \'published\';',
            ]),
            $filePath
        );
    }

    public function testMakeEnumUnit(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:enum', [
                'State',
                'cases' => 'Draft,Published',
                'namespace' => 'Example\Enums',
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $filePath = 'tmp/Enums/State.php';

        $this->assertFileMatchesFormat(
            Make::loadStub('enum', [
                '{namespace}' => 'Example\Enums',
                '{class}' => 'State',
                '{type}' => '',
                '{cases}' => '    case Draft;'.PHP_EOL.
                    '    case Published;',
            ]),
            $filePath
        );
    }

    public function testMakeEnumValueEscaping(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:enum', [
                'Status',
                'cases' => 'Draft:can\\\'t',
                'namespace' => 'Example\Enums',
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $filePath = 'tmp/Enums/Status.php';

        $this->assertFileMatchesFormat(
            Make::loadStub('enum', [
                '{namespace}' => 'Example\Enums',
                '{class}' => 'Status',
                '{type}' => ': string',
                '{cases}' => '    case Draft = \'can\\\\\\\'t\';',
            ]),
            $filePath
        );
    }
}
