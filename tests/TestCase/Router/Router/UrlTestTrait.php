<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Router;

use Fyre\Core\Config;
use Fyre\Http\ServerRequest;
use Fyre\Router\Exceptions\RouterException;
use Fyre\Router\Router;
use Tests\Mock\Controllers\HomeController;
use Tests\Mock\Entities\Item;
use Tests\Mock\Enums\State;
use Tests\Mock\Enums\Status;

trait UrlTestTrait
{
    public function testUrl(): void
    {
        $router = $this->container->use(Router::class);

        $router->connect('home', HomeController::class, as: 'home');

        $this->assertSame(
            '/home',
            $router->url('home')
        );
    }

    public function testUrlArguments(): void
    {
        $router = $this->container->use(Router::class);

        $router->connect('home/alternate/{a}/{b}/{c}', [HomeController::class, 'altMethod'], as: 'alternate');

        $this->assertSame(
            '/home/alternate/test/a/2',
            $router->url('alternate', [
                'a' => 'test',
                'b' => 'a',
                'c' => 2,
            ])
        );
    }

    public function testUrlArgumentsWithinSegment(): void
    {
        $router = $this->container->use(Router::class);

        $router->connect('files/{name}.{extension}', HomeController::class, as: 'files.view');

        $this->assertSame(
            '/files/archive.zip',
            $router->url('files.view', [
                'name' => 'archive',
                'extension' => 'zip',
            ])
        );
    }

    public function testUrlBackedEnumArgument(): void
    {
        $router = $this->container->use(Router::class);

        $router->connect('status/{status}', HomeController::class, as: 'status');

        $this->assertSame(
            '/status/draft',
            $router->url('status', [
                'status' => Status::Draft,
            ])
        );
    }

    public function testUrlEntityBackedEnumArgument(): void
    {
        $router = $this->container->use(Router::class);
        $item = new Item(['status' => Status::Draft], 'Items');

        $router->connect('items/{item:status}', HomeController::class, as: 'items.view');

        $this->assertSame(
            '/items/draft',
            $router->url('items.view', [
                'item' => $item,
            ])
        );
    }

    public function testUrlFragment(): void
    {
        $router = $this->container->use(Router::class);

        $router->connect('home/alternate/{a}', [HomeController::class, 'altMethod'], as: 'alternate');

        $this->assertSame(
            '/home/alternate/1#test',
            $router->url('alternate', [
                'a' => 1,
                '#' => 'test',
            ])
        );
    }

    public function testUrlFull(): void
    {
        $this->container->use(Config::class)->set('App.baseUri', 'https://test.com/');

        $router = $this->container->use(Router::class);

        $router->connect('home/alternate/{a}', [HomeController::class, 'altMethod'], as: 'alternate');

        $this->assertSame(
            'https://test.com/home/alternate/1#test',
            $router->url('alternate', [
                'a' => 1,
                '#' => 'test',
            ], full: true)
        );
    }

    public function testUrlFullOptions(): void
    {
        $this->container->use(Config::class)->set('App.baseUri', 'https://test.com/');

        $router = $this->container->use(Router::class);

        $router->connect(
            'home',
            HomeController::class,
            scheme: 'http',
            host: 'example.com',
            port: 8000,
            as: 'home'
        );

        $this->assertSame(
            'http://example.com:8000/home',
            $router->url('home', full: true)
        );
    }

    public function testUrlGroupAlias(): void
    {
        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router): void {
            $router->connect('alternate', [HomeController::class, 'altMethod'], as: 'alt');
        }, as: 'home.');

        $this->assertSame(
            '/alternate',
            $router->url('home.alt')
        );
    }

    public function testUrlGroupAliasDeep(): void
    {
        $router = $this->container->use(Router::class);

        $router->group(static function(Router $router): void {
            $router->group(static function(Router $router): void {
                $router->connect('alternate', [HomeController::class, 'altMethod'], as: 'alt');
            }, as: 'deep.');
        }, as: 'home.');

        $this->assertSame(
            '/alternate',
            $router->url('home.deep.alt')
        );
    }

    public function testUrlHostCaseInsensitive(): void
    {
        $router = $this->container->use(Router::class);
        $router->connect('home', HomeController::class, as: 'home');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'server' => [
                    'HTTP_HOST' => 'test.com',
                    'REQUEST_URI' => '/home',
                ],
            ],
        ]);

        $router->parseRequest($request);

        $this->assertSame(
            '/home',
            $router->url('home', host: 'TEST.COM')
        );
    }

    public function testUrlInvalid(): void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessageIs('Route alias `alternate` does not exist.');

        $router = $this->container->use(Router::class);

        $router->url('alternate');
    }

    public function testUrlInvalidArgument(): void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessageIs('Route parameter `a` is not valid.');

        $router = $this->container->use(Router::class);

        $router->connect(
            'home/alternate/{a}',
            [HomeController::class, 'altMethod'],
            placeholders: [
                'a' => '\d+',
            ],
            as: 'alternate'
        );

        $router->url('alternate', [
            'a' => 'test',
        ]);
    }

    public function testUrlInvalidArgumentLineFeed(): void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessageIs('Route parameter `a` is not valid.');

        $router = $this->container->use(Router::class);

        $router->connect(
            'home/alternate/{a}',
            [HomeController::class, 'altMethod'],
            placeholders: [
                'a' => '\d+',
            ],
            as: 'alternate'
        );

        $router->url('alternate', [
            'a' => "123\n",
        ]);
    }

    public function testUrlMissingArgument(): void
    {
        $this->expectException(RouterException::class);
        $this->expectExceptionMessageIs('Router parameter `c` is missing.');

        $router = $this->container->use(Router::class);

        $router->connect('home/alternate/{a}/{b}/{c}', [HomeController::class, 'altMethod'], as: 'alternate');

        $router->url('alternate', [
            'a' => 'test',
            'b' => 'a',
        ]);
    }

    public function testUrlOptionalArgument(): void
    {
        $router = $this->container->use(Router::class);

        $router->connect('home/alternate/{a}/{b?}', [HomeController::class, 'altMethod'], as: 'alternate');

        $this->assertSame(
            '/home/alternate/test',
            $router->url('alternate', [
                'a' => 'test',
            ])
        );
    }

    public function testUrlQuery(): void
    {
        $router = $this->container->use(Router::class);

        $router->connect('home/alternate/{a}', [HomeController::class, 'altMethod'], as: 'alternate');

        $this->assertSame(
            '/home/alternate/1?test=2',
            $router->url('alternate', [
                'a' => 1,
                '?' => ['test' => 2],
            ])
        );
    }

    public function testUrlUnitEnumArgument(): void
    {
        $router = $this->container->use(Router::class);

        $router->connect('state/{state}', HomeController::class, as: 'state');

        $this->assertSame(
            '/state/Draft',
            $router->url('state', [
                'state' => State::Draft,
            ])
        );
    }
}
