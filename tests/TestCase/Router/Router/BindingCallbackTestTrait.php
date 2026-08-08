<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Router;

use Fyre\Router\Route;
use Fyre\Router\Router;
use Tests\Mock\Controllers\HomeController;

trait BindingCallbackTestTrait
{
    public function testBindingCallbacks(): void
    {
        $callback = static function(string $value): string {
            return $value;
        };

        $router = $this->container->use(Router::class);

        $route = $router->get('test/{item}', HomeController::class, bindingCallbacks: [
            'item' => $callback,
        ]);

        $this->assertSame(
            [
                'item' => $callback,
            ],
            $route->getBindingCallbacks()
        );
    }

    public function testGroupBindingCallbacks(): void
    {
        $groupCallback = static function(string $value): string {
            return $value;
        };
        $routeCallback = static function(string $value): string {
            return $value;
        };

        $router = $this->container->use(Router::class);
        $route = null;

        $router->group(function(Router $router) use (&$route, $routeCallback): void {
            $route = $router->get('test/{item}/{post}', HomeController::class, bindingCallbacks: [
                'item' => $routeCallback,
            ]);
        }, bindingCallbacks: [
            'item' => $groupCallback,
            'post' => $groupCallback,
        ]);

        $this->assertInstanceOf(
            Route::class,
            $route
        );

        $this->assertSame(
            [
                'item' => $routeCallback,
                'post' => $groupCallback,
            ],
            $route->getBindingCallbacks()
        );
    }
}
