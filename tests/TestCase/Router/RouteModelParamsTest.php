<?php
declare(strict_types=1);

namespace Tests\TestCase\Router;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\TypeParser;
use Fyre\Http\ClientResponse;
use Fyre\Http\Exceptions\NotFoundException;
use Fyre\Http\MiddlewareQueue;
use Fyre\Http\RequestHandler;
use Fyre\Http\ServerRequest;
use Fyre\ORM\EntityLocator;
use Fyre\ORM\ModelRegistry;
use Fyre\Router\Middleware\RouterMiddleware;
use Fyre\Router\Middleware\SubstituteBindingsMiddleware;
use Fyre\Router\RouteHandler;
use Fyre\Router\Router;
use Fyre\Utility\Inflector;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Controllers\ItemsController;
use Tests\Mock\Entities\Child;
use Tests\Mock\Entities\Item;

use function getenv;

final class RouteModelParamsTest extends TestCase
{
    protected Container $container;

    protected Connection $db;

    protected ModelRegistry $modelRegistry;

    protected Router $router;

    public function testProcessClosureRouteModelParams(): void
    {
        $ran = false;

        $destination = function(Item $item) use (&$ran): string {
            $ran = true;

            $this->assertSame(
                'Test',
                $item->name
            );

            return '';
        };

        $this->router->connect('test/{item}', $destination);

        $queue = new MiddlewareQueue([
            RouterMiddleware::class,
            SubstituteBindingsMiddleware::class,
        ]);

        $routeHandler = $this->container->build(RouteHandler::class);
        $handler = $this->container->build(RequestHandler::class, [
            'queue' => $queue,
            'fallbackHandler' => $routeHandler,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test/1',
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertTrue($ran);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );
    }

    public function testProcessClosureRouteModelParamsInvalid(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Not Found');

        $destination = static function(Item $item): string {
            return '';
        };

        $this->router->connect('test/{item}', $destination);

        $queue = new MiddlewareQueue([
            RouterMiddleware::class,
            SubstituteBindingsMiddleware::class,
        ]);

        $routeHandler = $this->container->build(RouteHandler::class);
        $handler = $this->container->build(RequestHandler::class, [
            'queue' => $queue,
            'fallbackHandler' => $routeHandler,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test/2',
                ],
            ],
        ]);

        $handler->handle($request);
    }

    public function testProcessClosureRouteModelParamsNullable(): void
    {
        $ran = false;

        $destination = function(Item|null $item = null) use (&$ran): string {
            $ran = true;

            $this->assertNull($item);

            return '';
        };

        $this->router->connect('test/{item?}', $destination);

        $queue = new MiddlewareQueue([
            RouterMiddleware::class,
            SubstituteBindingsMiddleware::class,
        ]);

        $routeHandler = $this->container->build(RouteHandler::class);
        $handler = $this->container->build(RequestHandler::class, [
            'queue' => $queue,
            'fallbackHandler' => $routeHandler,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test',
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertTrue($ran);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );
    }

    public function testProcessClosureRouteModelParamsParent(): void
    {
        $ran = false;

        $destination = function(Item $item, Child $child) use (&$ran): string {
            $ran = true;

            $this->assertSame(
                'Test',
                $item->name
            );

            $this->assertSame(
                2,
                $child->value
            );

            return '';
        };

        $this->router->connect('test/{item}/{child}', $destination);

        $queue = new MiddlewareQueue([
            RouterMiddleware::class,
            SubstituteBindingsMiddleware::class,
        ]);

        $routeHandler = $this->container->build(RouteHandler::class);
        $handler = $this->container->build(RequestHandler::class, [
            'queue' => $queue,
            'fallbackHandler' => $routeHandler,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test/1/1',
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertTrue($ran);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );
    }

    public function testProcessClosureRouteModelParamsParentInvalid(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Not Found');

        $destination = static function(Item $item, Child $child): string {
            return '';
        };

        $this->router->connect('test/{item}/{child}', $destination);

        $queue = new MiddlewareQueue([
            RouterMiddleware::class,
            SubstituteBindingsMiddleware::class,
        ]);

        $routeHandler = $this->container->build(RouteHandler::class);
        $handler = $this->container->build(RequestHandler::class, [
            'queue' => $queue,
            'fallbackHandler' => $routeHandler,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test/1/2',
                ],
            ],
        ]);

        $handler->handle($request);
    }

    public function testProcessControllerRouteModelParams(): void
    {
        $this->router->connect('test/{item}', ItemsController::class);

        $queue = new MiddlewareQueue([
            RouterMiddleware::class,
            SubstituteBindingsMiddleware::class,
        ]);

        $routeHandler = $this->container->build(RouteHandler::class);
        $handler = $this->container->build(RequestHandler::class, [
            'queue' => $queue,
            'fallbackHandler' => $routeHandler,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test/1',
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );

        $this->assertSame(
            'Test',
            $response->getBody()->getContents()
        );
    }

    public function testProcessControllerRouteModelParamsInvalid(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Not Found');

        $this->router->connect('test/{item}', ItemsController::class);

        $queue = new MiddlewareQueue([
            RouterMiddleware::class,
            SubstituteBindingsMiddleware::class,
        ]);

        $routeHandler = $this->container->build(RouteHandler::class);
        $handler = $this->container->build(RequestHandler::class, [
            'queue' => $queue,
            'fallbackHandler' => $routeHandler,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test/2',
                ],
            ],
        ]);

        $handler->handle($request);
    }

    public function testProcessControllerRouteModelParamsNullable(): void
    {
        $this->router->connect('test/{item?}', [ItemsController::class, 'test']);

        $queue = new MiddlewareQueue([
            RouterMiddleware::class,
            SubstituteBindingsMiddleware::class,
        ]);

        $routeHandler = $this->container->build(RouteHandler::class);
        $handler = $this->container->build(RequestHandler::class, [
            'queue' => $queue,
            'fallbackHandler' => $routeHandler,
        ]);
        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/test',
                ],
            ],
        ]);

        $response = $handler->handle($request);

        $this->assertInstanceOf(
            ClientResponse::class,
            $response
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(TypeParser::class);
        $this->container->singleton(Config::class);
        $this->container->singleton(Inflector::class);
        $this->container->singleton(ConnectionManager::class);
        $this->container->singleton(SchemaRegistry::class);
        $this->container->singleton(ModelRegistry::class);
        $this->container->singleton(EntityLocator::class);
        $this->container->singleton(Router::class);
        $this->container->use(Config::class)
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

        $this->modelRegistry = $this->container->use(ModelRegistry::class);
        $this->modelRegistry->addNamespace('Tests\Mock\Models');

        $this->container->use(EntityLocator::class)->addNamespace('Tests\Mock\Entities');

        $this->db = $this->container->use(ConnectionManager::class)->use();

        $this->router = $this->container->use(Router::class);

        $this->db->query('DROP TABLE IF EXISTS items');
        $this->db->query('DROP TABLE IF EXISTS children');

        $this->db->query(<<<'EOT'
            CREATE TABLE items (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        EOT);

        $this->db->query(<<<'EOT'
            CREATE TABLE children (
                id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                item_id INT(10) UNSIGNED NOT NULL,
                value INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (id)
            ) COLLATE='utf8mb4_unicode_ci' ENGINE=InnoDB
        EOT);

        $Items = $this->modelRegistry->use('Items');
        $Children = $this->modelRegistry->use('Children');

        $routeItem = $Items->newEntity([
            'name' => 'Test',
        ]);

        $Items->save($routeItem);

        $routeChildren = $Children->newEntities([
            [
                'item_id' => 1,
                'value' => 2,
            ],
            [
                'item_id' => 2,
                'value' => 3,
            ],
        ]);

        $Children->saveMany($routeChildren);
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->db->query('DROP TABLE IF EXISTS items');
        $this->db->query('DROP TABLE IF EXISTS children');

        $this->db->disconnect();
    }
}
