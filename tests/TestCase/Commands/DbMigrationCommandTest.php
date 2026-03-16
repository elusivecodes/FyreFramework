<?php
declare(strict_types=1);

namespace Tests\TestCase\Commands;

use Fyre\Console\Command;
use Fyre\Console\CommandRunner;
use Fyre\Console\Console;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Loader;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Forge\ForgeRegistry;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\DB\Migration\MigrationRunner;
use Fyre\DB\Schema\Schema;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\TypeParser;
use Fyre\Event\EventManager;
use Fyre\Utility\Inflector;
use Fyre\Utility\Path;
use Override;
use PHPUnit\Framework\TestCase;

use function array_column;
use function fclose;
use function fopen;
use function getenv;

use const ROOT;

final class DbMigrationCommandTest extends TestCase
{
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

    protected MigrationRunner $migrationRunner;

    /**
     * @var resource
     */
    protected $output;

    protected Schema $schema;

    public function testDbMigrate(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('db:migrate', [
                'db' => ConnectionManager::DEFAULT,
            ])
        );

        $this->schema->clear();

        $this->assertTrue(
            $this->schema->hasTable('test1')
        );
        $this->assertTrue(
            $this->schema->hasTable('test2')
        );
        $this->assertTrue(
            $this->schema->hasTable('test3')
        );
    }

    public function testDbRollback(): void
    {
        $this->migrationRunner->migrate();

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('db:rollback', [
                'db' => ConnectionManager::DEFAULT,
            ])
        );

        $this->schema->clear();

        $this->assertFalse(
            $this->schema->hasTable('test1')
        );
        $this->assertFalse(
            $this->schema->hasTable('test2')
        );
        $this->assertFalse(
            $this->schema->hasTable('test3')
        );

        $this->assertSame(
            [],
            $this->migrationRunner->getHistory()->all()
        );
    }

    public function testDbRollbackBatches(): void
    {
        $this->migrationRunner->migrate();
        $this->migrationRunner->rollback(steps: 1);
        $this->migrationRunner->migrate();

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('db:rollback', [
                'db' => ConnectionManager::DEFAULT,
                'batches' => 1,
            ])
        );

        $this->schema->clear();

        $this->assertTrue(
            $this->schema->hasTable('test1')
        );
        $this->assertTrue(
            $this->schema->hasTable('test2')
        );
        $this->assertFalse(
            $this->schema->hasTable('test3')
        );

        $this->assertSame(
            [
                '2_Test2',
                '1_Test1',
            ],
            array_column($this->migrationRunner->getHistory()->all(), 'migration')
        );
    }

    public function testDbRollbackSteps(): void
    {
        $this->migrationRunner->migrate();

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('db:rollback', [
                'db' => ConnectionManager::DEFAULT,
                'steps' => 2,
            ])
        );

        $this->schema->clear();

        $this->assertTrue(
            $this->schema->hasTable('test1')
        );
        $this->assertFalse(
            $this->schema->hasTable('test2')
        );
        $this->assertFalse(
            $this->schema->hasTable('test3')
        );

        $this->assertSame(
            [
                '1_Test1',
            ],
            array_column($this->migrationRunner->getHistory()->all(), 'migration')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Loader::class);
        $container->singleton(Inflector::class);
        $container->singleton(Config::class);
        $container->singleton(EventManager::class);
        $container->singleton(CommandRunner::class);
        $container->singleton(TypeParser::class);
        $container->singleton(ConnectionManager::class);
        $container->singleton(ForgeRegistry::class);
        $container->singleton(MigrationRunner::class);
        $container->singleton(SchemaRegistry::class);

        $this->migrationRunner = $container->use(MigrationRunner::class);

        $this->db = $container->use(ConnectionManager::class)->setConfig(ConnectionManager::DEFAULT, [
            'className' => MysqlConnection::class,
            'host' => getenv('MYSQL_HOST'),
            'username' => getenv('MYSQL_USERNAME'),
            'password' => getenv('MYSQL_PASSWORD'),
            'database' => getenv('MYSQL_DATABASE'),
            'port' => getenv('MYSQL_PORT'),
            'collation' => 'utf8mb4_unicode_ci',
            'charset' => 'utf8mb4',
            'compress' => true,
        ])->use();

        $this->schema = $container->use(SchemaRegistry::class)->use($this->db);

        $container->use(Loader::class)->addNamespaces([
            'Fyre\Commands\\' => Path::normalize(Path::join(ROOT, 'src/Commands')),
            'Tests\Mock\Migrations' => 'tests/Mock/Migrations',
        ]);

        $this->migrationRunner->addNamespace('\Tests\Mock\Migrations');

        $this->input = fopen('php://memory', 'r+b');
        $this->output = fopen('php://memory', 'r+b');
        $this->error = fopen('php://memory', 'r+b');

        $container->instance(
            Console::class,
            new Console($this->input, $this->output, $this->error)
        );

        $this->commandRunner = $container->use(CommandRunner::class);
        $this->commandRunner->addNamespace('Fyre\Commands');
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->db->query('DROP TABLE IF EXISTS migrations');
        $this->db->query('DROP TABLE IF EXISTS test1');
        $this->db->query('DROP TABLE IF EXISTS test2');
        $this->db->query('DROP TABLE IF EXISTS test3');

        fclose($this->input);
        fclose($this->output);
        fclose($this->error);
    }
}
