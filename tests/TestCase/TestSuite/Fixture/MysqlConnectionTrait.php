<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Fixture;

use Fyre\Core\Config;
use Fyre\Core\ErrorHandler;
use Fyre\Core\Loader;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\ORM\EntityLocator;
use Fyre\ORM\ModelRegistry;
use Fyre\TestSuite\Fixture\Fixture;
use Fyre\TestSuite\Fixture\FixtureRegistry;
use Override;
use Tests\Mock\Application;

use function getenv;

trait MysqlConnectionTrait
{
    protected Connection $db;

    protected Fixture $fixture;

    protected FixtureRegistry $fixtureRegistry;

    protected ModelRegistry $modelRegistry;

    #[Override]
    public static function setUpBeforeClass(): void
    {
        $loader = new Loader();
        $app = new Application($loader);

        Application::setInstance($app);
    }

    #[Override]
    public static function tearDownAfterClass(): void
    {
        $app = Application::getInstance();

        $app->use(ConnectionManager::class)->use()->disconnect();
        $app->use(ErrorHandler::class)->unregister();
    }

    #[Override]
    protected function setUp(): void
    {
        $app = Application::getInstance();

        $app->use(Config::class)
            ->set('App.locale', 'en')
            ->set('Database', [
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

        $this->modelRegistry = $app->use(ModelRegistry::class);
        $this->modelRegistry->addNamespace('Tests\Mock\Models');

        $this->fixtureRegistry = $app->use(FixtureRegistry::class);
        $this->fixtureRegistry->clearNamespaces();
        $this->fixtureRegistry->addNamespace('Tests\Mock\Fixtures');

        $this->fixture = $this->fixtureRegistry->use('Items');

        $app->use(EntityLocator::class)->addNamespace('Tests\Mock\Entities');

        $this->db = $app->use(ConnectionManager::class)->use();

        $this->db->query('DROP TABLE IF EXISTS items');

        $this->db->query(<<<'EOT'
            CREATE TABLE items (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        EOT);

        parent::setUp();
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();

        $this->db->query('DROP TABLE IF EXISTS items');
    }
}
