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
use Fyre\ORM\EntityLocator;
use Fyre\ORM\ModelRegistry;
use Fyre\Router\Router;
use Fyre\Utility\Inflector;
use Override;
use Tests\Mock\Http\Session\Handlers\MockSessionHandler;

use function getenv;
use function password_hash;

use const PASSWORD_DEFAULT;

trait ConnectionTrait
{
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

        $this->auth->login($authUser);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(TypeParser::class);
        $this->container->singleton(ConnectionManager::class);
        $this->container->singleton(Config::class);
        $this->container->singleton(Session::class);
        $this->container->singleton(Inflector::class);
        $this->container->singleton(SchemaRegistry::class);
        $this->container->singleton(ModelRegistry::class);
        $this->container->singleton(EntityLocator::class);
        $this->container->singleton(MiddlewareRegistry::class);
        $this->container->singleton(PolicyRegistry::class);
        $this->container->singleton(Session::class);
        $this->container->singleton(Router::class);
        $this->container->singleton(Auth::class);
        $this->container->use(Config::class)->set('Database', [
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
        $this->container->use(Config::class)->set('Session', [
            'handler' => [
                'className' => MockSessionHandler::class,
            ],
        ]);
        $this->container->use(Config::class)->set('Auth.identifier', [
            'identifierFields' => ['username', 'email'],
        ]);

        $this->container->use(Router::class)->get('login', static fn(): string => '', as: 'login');

        $this->modelRegistry = $this->container->use(ModelRegistry::class);
        $this->modelRegistry->addNamespace('Tests\Mock\Models');

        $this->container->use(EntityLocator::class)->addNamespace('Tests\Mock\Entities');
        $this->container->use(PolicyRegistry::class)->addNamespace('Tests\Mock\Policies');

        $this->db = $this->container->use(ConnectionManager::class)->use();

        $this->container->use(MiddlewareRegistry::class)
            ->map('auth', AuthMiddleware::class)
            ->map('authenticated', AuthenticatedMiddleware::class)
            ->map('authorized', AuthorizedMiddleware::class)
            ->map('unauthenticated', UnauthenticatedMiddleware::class);

        $this->db->query('DROP TABLE IF EXISTS posts');
        $this->db->query('DROP TABLE IF EXISTS users');

        $this->db->query(<<<'EOT'
            CREATE TABLE posts (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id INT(10) UNSIGNED NOT NULL,
                content VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_unicode_ci',
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        EOT);

        $this->db->query(<<<'EOT'
            CREATE TABLE users (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                username VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_unicode_ci',
                email VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_unicode_ci',
                password VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_unicode_ci',
                token VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb3_unicode_ci',
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        EOT);

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
        $this->db->query('DROP TABLE IF EXISTS posts');
        $this->db->query('DROP TABLE IF EXISTS users');
    }
}
