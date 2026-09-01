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
use Fyre\View\HelperRegistry;
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

final class MakeHelperTest extends TestCase
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

    public function testMakeHelper(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:helper', ['Example'])
        );

        rewind($this->output);
        $this->assertSame(
            "\033[0;32mGenerated: tmp/Helpers/ExampleHelper.php\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $filePath = 'tmp/Helpers/ExampleHelper.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('helper', [
                '{namespace}' => 'Example\Helpers',
                '{class}' => 'ExampleHelper',
            ]),
            $filePath
        );
    }

    public function testMakeHelperExistingFile(): void
    {
        $filePath = 'tmp/Helpers/ExampleHelper.php';
        @mkdir('tmp/Helpers', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:helper', ['Example'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            "\033[0;31mHelper file already exists.\033[0m".PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertStringEqualsFile($filePath, 'changed');
    }

    public function testMakeHelperForce(): void
    {
        $filePath = 'tmp/Helpers/ExampleHelper.php';
        @mkdir('tmp/Helpers', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:helper', [
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
            Make::loadStub('helper', [
                '{namespace}' => 'Example\Helpers',
                '{class}' => 'ExampleHelper',
            ]),
            $filePath
        );
    }

    public function testMakeHelperNamespaceNotFound(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:helper', [
                'Example',
                'namespace' => 'Missing\Helpers',
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            "\033[0;31mNamespace path not found.\033[0m".PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertFileDoesNotExist('tmp/Helpers/ExampleHelper.php');
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
        $container->singleton(HelperRegistry::class);

        $tmpDir = Path::join(ROOT, 'tmp');

        $container->use(Loader::class)->addNamespaces([
            'Example\\' => $tmpDir,
            'Fyre\Commands\\' => Path::join(ROOT, 'src/Commands'),
        ]);
        $container->use(HelperRegistry::class)->addNamespace('Example\Helpers');

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
        @unlink('tmp/Helpers/ExampleHelper.php');
        @rmdir('tmp/Helpers');
        @rmdir('tmp');

        fclose($this->input);
        fclose($this->output);
        fclose($this->error);
    }
}
