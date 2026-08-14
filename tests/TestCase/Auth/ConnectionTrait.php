<?php
declare(strict_types=1);

namespace Tests\TestCase\Auth;

use Fyre\Auth\Access;
use Fyre\Auth\Auth;
use Fyre\Auth\Identifier;
use Fyre\Auth\Middleware\AuthenticatedMiddleware;
use Fyre\Auth\Middleware\AuthMiddleware;
use Fyre\Auth\Middleware\AuthorizedMiddleware;
use Fyre\Auth\Middleware\UnauthenticatedMiddleware;
use Fyre\Auth\PolicyRegistry;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\TypeParser;
use Fyre\Http\MiddlewareRegistry;
use Fyre\Http\Session\Session;
use Fyre\ORM\Entity;
use Fyre\ORM\EntityLocator;
use Fyre\ORM\ModelRegistry;
use Fyre\Router\Router;
use Fyre\Utility\Inflector;
use Override;
use Tests\Mock\Http\Session\Handlers\MockSessionHandler;
use Tests\TestCase\Shared\DatabaseLifecycleTrait;

use function getenv;
use function password_hash;

use const PASSWORD_DEFAULT;

trait ConnectionTrait
{
    use DatabaseLifecycleTrait;

    protected Access $access;

    protected Auth $auth;

    protected Container $container;

    protected Connection $db;

    protected Identifier $identifier;

    protected ModelRegistry $modelRegistry;

    protected Session $session;

    protected function login(): void
    {
        $authUser = $this->identifier->getModel()
            ->find()
            ->where(['Users.id' => 1])
            ->first();

        $this->assertInstanceOf(
            Entity::class,
            $authUser
        );

        $this->auth->login($authUser);
    }

    protected static function buildContainer(): Container
    {
        $container = new Container();
        $container->singleton(TypeParser::class);
        $container->singleton(ConnectionManager::class);
        $container->singleton(Config::class);
        $container->singleton(Session::class);
        $container->singleton(Inflector::class);
        $container->singleton(SchemaRegistry::class);
        $container->singleton(ModelRegistry::class);
        $container->singleton(EntityLocator::class);
        $container->singleton(MiddlewareRegistry::class);
        $container->singleton(PolicyRegistry::class);
        $container->singleton(Router::class);
        $container->singleton(Auth::class);
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
        $container->use(Config::class)->set('Session', [
            'handler' => [
                'className' => MockSessionHandler::class,
            ],
        ]);
        $container->use(Config::class)->set('Auth.identifier', [
            'identifierFields' => ['username', 'email'],
        ]);

        return $container;
    }

    protected static function clearSchema(Connection $db): void
    {
        $db->query('DROP TABLE IF EXISTS posts');
        $db->query('DROP TABLE IF EXISTS users');
    }

    protected static function createSchema(Connection $db): void
    {
        $db->query(<<<'SQL'
            CREATE TABLE posts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id INT(10) UNSIGNED NOT NULL,
                content VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_unicode_ci',
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);

        $db->query(<<<'SQL'
            CREATE TABLE users (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                username VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_unicode_ci',
                email VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_unicode_ci',
                password VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_unicode_ci',
                token VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_unicode_ci',
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        SQL);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = static::buildContainer();

        $this->container->use(Router::class)->get('login', static fn(): string => '', as: 'login');

        $this->modelRegistry = $this->container->use(ModelRegistry::class);
        $this->modelRegistry->addNamespace('Tests\Mock\Models');

        $this->container->use(EntityLocator::class)->addNamespace('Tests\Mock\Entities');
        $this->container->use(PolicyRegistry::class)->addNamespace('Tests\Mock\Policies');

        $this->db = $this->container->use(ConnectionManager::class)->use();

        $this->db->truncate('posts');
        $this->db->truncate('users');

        $this->container->use(MiddlewareRegistry::class)
            ->map('auth', AuthMiddleware::class)
            ->map('authenticated', AuthenticatedMiddleware::class)
            ->map('authorized', AuthorizedMiddleware::class)
            ->map('unauthenticated', UnauthenticatedMiddleware::class);

        $_SESSION = [];

        $this->session = $this->container->use(Session::class);

        $this->session->start();

        $this->auth = $this->container->use(Auth::class);
        $this->access = $this->auth->access();
        $this->identifier = $this->auth->identifier();

        $Users = $this->modelRegistry->use('Users');

        $authUser = $Users->newEntity([
            'username' => 'test',
            'email' => 'test@test.com',
            'password' => password_hash('test', PASSWORD_DEFAULT),
            'token' => 'Ew7tqx8kH6QsNe8SS0tVT0BX2LIRVQyl',
        ]);

        $Users->save($authUser);

        $Posts = $this->modelRegistry->use('Posts');

        $authPosts = $Posts->newEntities([
            [
                'user_id' => 1,
                'content' => 'test 1',
            ],
            [
                'user_id' => 2,
                'content' => 'test 2',
            ],
        ]);

        $Posts->saveMany($authPosts);
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->db->disconnect();
    }
}
