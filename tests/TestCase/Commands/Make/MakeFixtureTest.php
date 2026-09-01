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
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Handlers\Sqlite\SqliteConnection;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\TypeParser;
use Fyre\Event\EventManager;
use Fyre\ORM\EntityLocator;
use Fyre\ORM\ModelRegistry;
use Fyre\TestSuite\Fixture\FixtureRegistry;
use Fyre\Utility\Inflector;
use Fyre\Utility\Path;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\TestCase\Shared\DatabaseLifecycleTrait;

use function fclose;
use function file_put_contents;
use function fopen;
use function implode;
use function mkdir;
use function rewind;
use function rmdir;
use function stream_get_contents;
use function unlink;

use const ROOT;

final class MakeFixtureTest extends TestCase
{
    use DatabaseLifecycleTrait;

    protected CommandRunner $commandRunner;

    protected Connection $db;

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

    public function testMakeFixture(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:fixture', ['Example'])
        );

        rewind($this->output);
        $this->assertSame(
            "\033[0;32mGenerated: tmp/Fixtures/ExampleFixture.php\033[0m".PHP_EOL,
            stream_get_contents($this->output)
        );
        rewind($this->error);
        $this->assertSame('', stream_get_contents($this->error));

        $filePath = 'tmp/Fixtures/ExampleFixture.php';

        $this->assertFileExists($filePath);

        $this->assertFileMatchesFormat(
            Make::loadStub('fixture', [
                '{namespace}' => 'Example\Fixtures',
                '{class}' => 'ExampleFixture',
                '{data}' => '        //',
            ]),
            $filePath
        );
    }

    public function testMakeFixtureData(): void
    {
        $this->db->query(<<<'SQL'
            INSERT INTO example (id, name, metadata, created) VALUES
                (2, 'Second', '{"enabled":false}', '2024-02-03 04:05:06'),
                (1, 'First', '{"enabled":true,"tags":["one","two"]}', '2024-01-02 03:04:05')
        SQL);

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:fixture', [
                'Example',
                'data' => true,
                'limit' => 1,
            ])
        );

        $this->assertFileMatchesFormat(
            Make::loadStub('fixture', [
                '{namespace}' => 'Example\Fixtures',
                '{class}' => 'ExampleFixture',
                '{data}' => implode(PHP_EOL, [
                    '        [',
                    '            \'id\' => 1,',
                    '            \'name\' => \'First\',',
                    '            \'metadata\' => [',
                    '                \'enabled\' => true,',
                    '                \'tags\' => [',
                    '                    \'one\',',
                    '                    \'two\',',
                    '                ],',
                    '            ],',
                    '            \'created\' => \'2024-01-02T03:04:05.000+00:00\',',
                    '        ],',
                ]),
            ]),
            'tmp/Fixtures/ExampleFixture.php'
        );
    }

    public function testMakeFixtureExistingFile(): void
    {
        $filePath = 'tmp/Fixtures/ExampleFixture.php';
        @mkdir('tmp/Fixtures', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:fixture', ['Example'])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            "\033[0;31mFixture file already exists.\033[0m".PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertStringEqualsFile($filePath, 'changed');
    }

    public function testMakeFixtureForce(): void
    {
        $filePath = 'tmp/Fixtures/ExampleFixture.php';
        @mkdir('tmp/Fixtures', 0755, true);
        file_put_contents($filePath, 'changed');

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('make:fixture', [
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
            Make::loadStub('fixture', [
                '{namespace}' => 'Example\Fixtures',
                '{class}' => 'ExampleFixture',
                '{data}' => '        //',
            ]),
            $filePath
        );
    }

    public function testMakeFixtureNamespaceNotFound(): void
    {
        $this->assertSame(
            Command::CODE_ERROR,
            $this->commandRunner->run('make:fixture', [
                'Example',
                'namespace' => 'Missing\Fixtures',
            ])
        );

        rewind($this->output);
        $this->assertSame('', stream_get_contents($this->output));
        rewind($this->error);
        $this->assertSame(
            "\033[0;31mNamespace path not found.\033[0m".PHP_EOL,
            stream_get_contents($this->error)
        );
        $this->assertFileDoesNotExist('tmp/Fixtures/ExampleFixture.php');
    }

    #[Override]
    protected static function buildContainer(): Container
    {
        $container = new Container();
        $container->singleton(Loader::class);
        $container->singleton(Inflector::class);
        $container->singleton(Config::class);
        $container->singleton(EventManager::class);
        $container->singleton(TypeParser::class);
        $container->singleton(ConnectionManager::class);
        $container->singleton(SchemaRegistry::class);
        $container->singleton(CommandRunner::class);
        $container->singleton(Make::class);
        $container->singleton(EntityLocator::class);
        $container->singleton(FixtureRegistry::class);
        $container->singleton(ModelRegistry::class);
        $container->use(Config::class)->set('Database', [
            'default' => [
                'className' => SqliteConnection::class,
                'database' => ':memory:',
                'mode' => 'memory',
                'cache' => 'shared',
            ],
        ]);

        $tmpDir = Path::join(ROOT, 'tmp');

        $container->use(Loader::class)->addNamespaces([
            'Example\\' => $tmpDir,
            'Fyre\Commands\\' => Path::join(ROOT, 'src/Commands'),
        ]);
        $container->use(FixtureRegistry::class)->addNamespace('Example\Fixtures');
        $container->use(ModelRegistry::class)->addNamespace('Example\Models');

        return $container;
    }

    #[Override]
    protected static function clearSchema(Connection $db): void
    {
        $db->query('DROP TABLE IF EXISTS example');
    }

    #[Override]
    protected static function createSchema(Connection $db): void
    {
        $db->query(<<<'SQL'
            CREATE TABLE example (
                id INTEGER NOT NULL,
                name VARCHAR(255) NOT NULL,
                metadata JSON NOT NULL,
                created DATETIME NOT NULL,
                PRIMARY KEY (id)
            )
        SQL);
    }

    #[Override]
    protected function setUp(): void
    {
        $container = self::buildContainer();

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

        $this->db = $container->use(ConnectionManager::class)->use();
        $this->db->truncate('example');
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->db->disconnect();

        @unlink('tmp/Fixtures/ExampleFixture.php');
        @rmdir('tmp/Fixtures');
        @rmdir('tmp');

        fclose($this->input);
        fclose($this->output);
        fclose($this->error);
    }
}
