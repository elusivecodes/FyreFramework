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

final class DbLockCommandTest extends TestCase
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

    /**
     * @var resource
     */
    protected $output;

    protected Schema $schema;

    public function testDbLockPrune(): void
    {
        $this->commandRunner->run('db:lock:setup', [
            'db' => ConnectionManager::DEFAULT,
        ]);

        $this->db
            ->insert()
            ->into('fyre__locks')
            ->values([
                [
                    'name' => 'active',
                    'owner' => 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa',
                    'expires' => '2999-01-01 00:00:00',
                ],
                [
                    'name' => 'expired',
                    'owner' => 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb',
                    'expires' => '2000-01-01 00:00:00',
                ],
            ])
            ->execute();

        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('db:lock:prune', [
                'db' => ConnectionManager::DEFAULT,
            ])
        );

        $locks = $this->db
            ->select(['name'])
            ->from('fyre__locks')
            ->orderBy('name ASC')
            ->execute()
            ->all();

        $this->assertArraysAreIdentical(
            ['active'],
            array_column($locks, 'name')
        );
    }

    public function testDbLockPruneWithoutTable(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('db:lock:prune', [
                'db' => ConnectionManager::DEFAULT,
            ])
        );

        $this->assertFalse(
            $this->schema->hasTable('fyre__locks')
        );
    }

    public function testDbLockSetup(): void
    {
        $this->assertSame(
            Command::CODE_SUCCESS,
            $this->commandRunner->run('db:lock:setup', [
                'db' => ConnectionManager::DEFAULT,
            ])
        );

        $this->schema->clear();

        $this->assertTrue(
            $this->schema->hasTable('fyre__locks')
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
        $container->singleton(SchemaRegistry::class);
        $container->use(Config::class)->set('Database', [
            'default' => [
                'className' => MysqlConnection::class,
                'host' => getenv('MYSQL_HOST'),
                'username' => getenv('MYSQL_USERNAME'),
                'password' => getenv('MYSQL_PASSWORD'),
                'database' => getenv('MYSQL_DATABASE'),
                'port' => getenv('MYSQL_PORT'),
                'collation' => 'utf8mb4_unicode_ci',
                'charset' => 'utf8mb4',
                'compress' => true,
            ],
        ]);

        $this->db = $container->use(ConnectionManager::class)->use();
        $this->schema = $container->use(SchemaRegistry::class)->use($this->db);

        $container->use(Loader::class)->addNamespaces([
            'Fyre\Commands\\' => Path::join(ROOT, 'src/Commands'),
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
        $this->db->query('DROP TABLE IF EXISTS fyre__locks');

        fclose($this->input);
        fclose($this->output);
        fclose($this->error);
    }
}
