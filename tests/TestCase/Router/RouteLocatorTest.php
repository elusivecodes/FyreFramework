<?php
declare(strict_types=1);

namespace Tests\TestCase\Router;

use Fyre\Cache\CacheManager;
use Fyre\Cache\Handlers\FileCacher;
use Fyre\Core\Container;
use Fyre\Core\Loader;
use Fyre\Http\ServerRequest;
use Fyre\ORM\ModelRegistry;
use Fyre\Router\RouteLocator;
use Fyre\Router\Router;
use Fyre\Router\Routes\ControllerRoute;
use Fyre\Utility\Inflector;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Controllers\Locate\CommentsController;
use Tests\Mock\Controllers\Locate\DashboardController;
use Tests\Mock\Controllers\Locate\ParentCategory\ChildItemsController;
use Tests\Mock\Controllers\Locate\PostsController;

use function mkdir;
use function rmdir;
use function unlink;

final class RouteLocatorTest extends TestCase
{
    protected Container $container;

    protected RouteLocator $routeLocator;

    public function testCacheRoutes(): void
    {
        $this->routeLocator->discover([
            'Tests\Mock\Controllers\Locate',
        ]);

        $this->assertSame(
            [
                [
                    'path' => 'comments/create',
                    'destination' => [CommentsController::class, 'create'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['POST'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'comments.create',
                ],
                [
                    'path' => 'comments/delete/{comment}',
                    'destination' => [CommentsController::class, 'delete'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['DELETE'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'comments.delete',
                ],
                [
                    'path' => 'comments/get/{comment}',
                    'destination' => [CommentsController::class, 'get'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['GET'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'comments.get',
                ],
                [
                    'path' => 'comments',
                    'destination' => [CommentsController::class, 'index'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['GET'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'comments.index',
                ],
                [
                    'path' => 'comments/update/{comment}',
                    'destination' => [CommentsController::class, 'update'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['PATCH', 'PUT'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'comments.update',
                ],
                [
                    'path' => '/',
                    'destination' => [DashboardController::class, 'index'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['GET'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'dashboard.index',
                ],
                [
                    'path' => 'parent-category/child-items/do-something',
                    'destination' => [
                        ChildItemsController::class,
                        'doSomething',
                    ],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['GET'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'parent-category.child-items.do-something',
                ],
                [
                    'path' => 'posts',
                    'destination' => [PostsController::class, 'create'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['POST'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'posts.create',
                ],
                [
                    'path' => 'posts/{post}',
                    'destination' => [PostsController::class, 'delete'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['DELETE'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'posts.delete',
                ],
                [
                    'path' => 'posts/{post}',
                    'destination' => [PostsController::class, 'get'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['GET'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'posts.get',
                ],
                [
                    'path' => 'posts',
                    'destination' => [PostsController::class, 'index'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['GET'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'posts.index',
                ],
                [
                    'path' => 'posts/{post?}',
                    'destination' => [PostsController::class, 'put'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['PUT'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'posts.put',
                ],
                [
                    'path' => 'posts/{post}',
                    'destination' => [PostsController::class, 'update'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['PATCH'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'posts.update',
                ],
            ],
            $this->container->use(CacheManager::class)
                ->use('_routes')
                ->get('Tests.Mock.Controllers.Locate')
        );
    }

    public function testDiscover(): void
    {
        $routes = $this->routeLocator->discover([
            'Tests\Mock\Controllers\Locate',
        ]);

        $this->assertSame(
            [
                [
                    'path' => 'parent-category/child-items/do-something',
                    'destination' => [
                        ChildItemsController::class,
                        'doSomething',
                    ],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['GET'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'parent-category.child-items.do-something',
                ],
                [
                    'path' => 'comments/delete/{comment}',
                    'destination' => [CommentsController::class, 'delete'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['DELETE'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'comments.delete',
                ],
                [
                    'path' => 'comments/update/{comment}',
                    'destination' => [CommentsController::class, 'update'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['PATCH', 'PUT'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'comments.update',
                ],
                [
                    'path' => 'comments/get/{comment}',
                    'destination' => [CommentsController::class, 'get'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['GET'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'comments.get',
                ],
                [
                    'path' => 'comments/create',
                    'destination' => [CommentsController::class, 'create'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['POST'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'comments.create',
                ],
                [
                    'path' => 'posts/{post?}',
                    'destination' => [PostsController::class, 'put'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['PUT'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'posts.put',
                ],
                [
                    'path' => 'posts/{post}',
                    'destination' => [PostsController::class, 'delete'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['DELETE'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'posts.delete',
                ],
                [
                    'path' => 'posts/{post}',
                    'destination' => [PostsController::class, 'get'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['GET'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'posts.get',
                ],
                [
                    'path' => 'posts/{post}',
                    'destination' => [PostsController::class, 'update'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['PATCH'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'posts.update',
                ],
                [
                    'path' => 'comments',
                    'destination' => [CommentsController::class, 'index'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['GET'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'comments.index',
                ],
                [
                    'path' => 'posts',
                    'destination' => [PostsController::class, 'create'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['POST'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'posts.create',
                ],
                [
                    'path' => 'posts',
                    'destination' => [PostsController::class, 'index'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['GET'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'posts.index',
                ],
                [
                    'path' => '/',
                    'destination' => [DashboardController::class, 'index'],
                    'scheme' => null,
                    'host' => null,
                    'port' => null,
                    'methods' => ['GET'],
                    'middleware' => [],
                    'placeholders' => [],
                    'as' => 'dashboard.index',
                ],
            ],
            $routes
        );
    }

    public function testDiscoverRoutes(): void
    {
        $router = $this->container->use(Router::class);

        $router->discoverRoutes([
            'Tests\Mock\Controllers\Locate',
        ]);

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'REQUEST_URI' => '/comments',
                ],
            ],
        ]);

        $route = $router->parseRequest($request)->getAttribute('route');

        $this->assertInstanceOf(
            ControllerRoute::class,
            $route
        );

        $this->assertSame(
            CommentsController::class,
            $route->getController()
        );

        $this->assertSame(
            'index',
            $route->getAction()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(Loader::class);
        $this->container->singleton(ModelRegistry::class);
        $this->container->singleton(CacheManager::class);
        $this->container->singleton(Inflector::class);
        $this->container->singleton(Router::class);
        $this->container->singleton(RouteLocator::class);

        $this->container->use(CacheManager::class)->setConfig('_routes', [
            'className' => FileCacher::class,
            'path' => 'tmp',
            'prefix' => 'routes.',
            'expire' => 3600,
        ]);

        $this->container->use(Loader::class)->addNamespaces([
            'Tests' => 'tests',
        ]);

        $this->routeLocator = $this->container->use(RouteLocator::class);

        @mkdir('tmp');
    }

    #[Override]
    protected function tearDown(): void
    {
        @unlink('tmp/routes.Tests.Mock.Controllers.Locate');
        @rmdir('tmp');
    }
}
