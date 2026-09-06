<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Router;

use Fyre\Core\Config;
use Fyre\Http\ServerRequest;
use Fyre\Router\Exceptions\RouterException;
use Fyre\Router\Router;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Mock\Controllers\HomeController;
use Tests\Mock\Entities\Item;
use Tests\Mock\Enums\State;
use Tests\Mock\Enums\Status;

trait UrlTestTrait
{
    /**
     * @return array<string, array{string, int|null, string}>
     */
    public static function urlPortProvider(): array
    {
        return [
            'http implicit default' => ['http://example.com/current', 80, '/home'],
            'http explicit default' => ['http://example.com:80/current', 80, '/home'],
            'http different port' => ['http://example.com:8080/current', 80, 'http://example.com:80/home'],
            'http matching custom port' => ['http://example.com:8080/current', 8080, '/home'],
            'http missing custom port' => ['http://example.com/current', 8080, 'http://example.com:8080/home'],
            'http inherited default' => ['http://example.com/current', null, '/home'],
            'http inherited custom port' => ['http://example.com:8080/current', null, '/home'],
            'https implicit default' => ['https://example.com/current', 443, '/home'],
            'https explicit default' => ['https://example.com:443/current', 443, '/home'],
            'https different port' => ['https://example.com:8443/current', 443, 'https://example.com:443/home'],
            'https matching custom port' => ['https://example.com:8443/current', 8443, '/home'],
            'https missing custom port' => ['https://example.com/current', 8443, 'https://example.com:8443/home'],
            'https inherited default' => ['https://example.com/current', null, '/home'],
            'https inherited custom port' => ['https://example.com:8443/current', null, '/home'],
        ];
    }

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

    public function testUrlArgumentsEncoding(): void
    {
        $router = $this->container->use(Router::class);

        $router->connect('files/{name}', HomeController::class, as: 'files.view');

        $this->assertSame(
            '/files/caf%C3%A9%20report.txt',
            $router->url('files.view', [
                'name' => "caf\u{00e9} report.txt",
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

    public function testUrlFragmentEncoding(): void
    {
        $router = $this->container->use(Router::class);

        $router->connect('home', HomeController::class, as: 'home');

        $this->assertSame(
            '/home#section%20one%20?two%23three',
            $router->url('home', [
                '#' => 'section%20one ?two#three',
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
                'uri' => 'http://test.com/home',
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

    #[DataProvider('urlPortProvider')]
    public function testUrlPort(string $uri, int|null $port, string $expected): void
    {
        $router = $this->container->use(Router::class);
        $router->connect('current', HomeController::class);
        $router->connect('home', HomeController::class, port: $port, as: 'home');

        $request = $this->container->build(ServerRequest::class, [
            'options' => [
                'uri' => $uri,
            ],
        ]);

        $router->parseRequest($request);

        $this->assertSame($expected, $router->url('home'));
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
