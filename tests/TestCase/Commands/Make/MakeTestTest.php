<?php
declare(strict_types=1);

namespace Tests\TestCase\Commands\Make;

use Fyre\Console\Command;
use Fyre\Console\CommandRunner;
use Fyre\Console\Console;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Loader;
use Fyre\Core\Make;
use Fyre\DB\TypeParser;
use Fyre\Event\EventManager;
use Fyre\Utility\Inflector;
use Fyre\Utility\Path;
use Override;
use PHPUnit\Framework\TestCase;

use function fclose;
use function file_put_contents;
use function fopen;
use function mkdir;
use function rewind;
use function rmdir;
use function stream_get_contents;
use function unlink;

use const ROOT;

final class MakeTestTest extends TestCase
{
    protected CommandRunner $commandRunner;

    /**
     * @var resource
     */
    protected $error;

    /**
     * @var resource
     */
    protected $input;

    /**
     * @var resource
     */
    protected $output;

    public function testMakeTest(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:test', ['Example'])
        );

        rewind($this->output);
        $this->assertSame(
            "\033[0;32mGenerated: tmp/TestCase/ExampleTest.php\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $filePath = 'tmp/TestCase/ExampleTest.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('test', [
                '{namespace}' => 'Tests\TestCase',
                '{class}' => 'ExampleTest',
                '{body}' => '    //',
            ]),
            $filePath
        );
    }

    public function testMakeTestExistingFile(): void
    {
        $filePath = 'tmp/TestCase/ExampleTest.php';
        @mkdir('tmp/TestCase', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:test', ['Example'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            "\033[0;31mTest file already exists.\033[0m".PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertStringEqualsFile($filePath, 'changed');
    }

    public function testMakeTestFixture(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:test', [
                'Example',
                'fixture' => 'Example',
            ])
        );

        rewind($this->output);
        $this->assertSame(
            "\033[0;32mGenerated: tmp/TestCase/ExampleTest.php\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $filePath = 'tmp/TestCase/ExampleTest.php';

        $this->assertFileMatchesFormat(
            Make::loadStub('test', [
                '{namespace}' => 'Tests\TestCase',
                '{class}' => 'ExampleTest',
                '{body}' => '    protected array $fixtures = ['.PHP_EOL.
                    '        \'Example\','.PHP_EOL.
                    '    ];',
            ]),
            $filePath
        );
    }

    public function testMakeTestForce(): void
    {
        $filePath = 'tmp/TestCase/ExampleTest.php';
        @mkdir('tmp/TestCase', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:test', [
                'Example',
                'force' => true,
            ])
        );

        rewind($this->output);
        $this->assertSame(
            "\033[0;32mGenerated: ".$filePath."\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $this->assertFileMatchesFormat(
            Make::loadStub('test', [
                '{namespace}' => 'Tests\TestCase',
                '{class}' => 'ExampleTest',
                '{body}' => '    //',
            ]),
            $filePath
        );
    }

    public function testMakeTestNamespaceNotFound(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:test', [
                'Example',
                'namespace' => 'Missing\TestCase',
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            "\033[0;31mTest file namespace path not found.\033[0m".PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertFileDoesNotExist('tmp/TestCase/ExampleTest.php');
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Loader::class);
        $container->singleton(Inflector::class);
        $container->singleton(Config::class);
        $container->singleton(EventManager::class);
        $container->singleton(TypeParser::class);
        $container->singleton(CommandRunner::class);
        $container->singleton(Make::class);

        $tmpDir = Path::join(ROOT, 'tmp');

        $container->use(Loader::class)->addNamespaces([
            'Fyre\Commands\\' => Path::join(ROOT, 'src/Commands'),
            'Tests\\' => $tmpDir,
        ]);

        $input = fopen('php://memory', 'r+b');
        $output = fopen('php://memory', 'r+b');
        $error = fopen('php://memory', 'r+b');

        $this->assertIsResource($input);
        $this->assertIsResource($output);
        $this->assertIsResource($error);

        $this->input = $input;
        $this->output = $output;
        $this->error = $error;

        $container->instance(Console::class, new Console($input, $output, $error));

        $this->commandRunner = $container->use(CommandRunner::class);
        $this->commandRunner->addNamespace('Fyre\Commands');

        @mkdir('tmp');
    }

    #[Override]
    protected function tearDown(): void
    {
        @unlink('tmp/TestCase/ExampleTest.php');
        @rmdir('tmp/TestCase');
        @rmdir('tmp');

        fclose($this->input);
        fclose($this->output);
        fclose($this->error);
    }
}
