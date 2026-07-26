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
use Fyre\DB\Migration\MigrationRunner;
use Fyre\DB\TypeParser;
use Fyre\Event\EventManager;
use Fyre\Utility\Inflector;
use Fyre\Utility\Path;
use Override;
use PHPUnit\Framework\TestCase;

use function fclose;
use function file_put_contents;
use function fopen;
use function glob;
use function mkdir;
use function pathinfo;
use function rewind;
use function rmdir;
use function stream_get_contents;
use function unlink;

use const ROOT;

final class MakeMigrationTest extends TestCase
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

    public function testMakeMigration(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:migration', ['CreateTables', '20240101'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $filePath = 'tmp/Migrations/Migration_20240101_CreateTables.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('migration', [
                '{namespace}' => 'Example\Migrations',
                '{class}' => 'Migration_20240101_CreateTables',
            ]),
            $filePath
        );
    }

    public function testMakeMigrationDefaultVersion(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:migration', ['CreateTables'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $files = glob('tmp/Migrations/Migration_*_CreateTables.php');

        $this->assertIsArray($files);
        $this->assertCount(1, $files);

        $filePath = $files[0];
        $className = pathinfo($filePath, PATHINFO_FILENAME);

        $this->assertFileMatchesFormat(
            Make::loadStub('migration', [
                '{namespace}' => 'Example\Migrations',
                '{class}' => $className,
            ]),
            $filePath
        );
    }

    public function testMakeMigrationExistingFile(): void
    {
        $filePath = 'tmp/Migrations/Migration_20240101_CreateTables.php';
        @mkdir('tmp/Migrations', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:migration', ['CreateTables', '20240101'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            Console::style('Migration file already exists.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertStringEqualsFile($filePath, 'changed');
    }

    public function testMakeMigrationForce(): void
    {
        $filePath = 'tmp/Migrations/Migration_20240101_CreateTables.php';
        @mkdir('tmp/Migrations', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:migration', [
                'CreateTables',
                '20240101',
                'force' => true,
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $this->assertFileMatchesFormat(
            Make::loadStub('migration', [
                '{namespace}' => 'Example\Migrations',
                '{class}' => 'Migration_20240101_CreateTables',
            ]),
            $filePath
        );
    }

    public function testMakeMigrationNamespaceNotFound(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:migration', [
                'CreateTables',
                '20240101',
                'namespace' => 'Missing\Migrations',
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            Console::style('Namespace path not found.', Console::RED).PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertFileDoesNotExist('tmp/Migrations/Migration_20240101_CreateTables.php');
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
        $container->singleton(MigrationRunner::class);

        $tmpDir = Path::normalize(Path::join(ROOT, 'tmp'));

        $container->use(Loader::class)->addNamespaces([
            'Example\\' => $tmpDir,
            'Fyre\Commands\\' => Path::normalize(Path::join(ROOT, 'src/Commands')),
        ]);
        $container->use(MigrationRunner::class)->addNamespace('Example\Migrations');

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
        foreach (glob('tmp/Migrations/Migration_*_CreateTables.php') ?: [] as $filePath) {
            @unlink($filePath);
        }

        @rmdir('tmp/Migrations');
        @rmdir('tmp');

        fclose($this->input);
        fclose($this->output);
        fclose($this->error);
    }
}
