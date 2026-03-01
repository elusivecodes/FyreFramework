<?php
declare(strict_types=1);

namespace Tests\TestCase\View\Helpers;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Http\ServerRequest;
use Fyre\Router\Router;
use Fyre\View\CellRegistry;
use Fyre\View\HelperRegistry;
use Fyre\View\TemplateLocator;
use Fyre\View\View;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Controllers\HomeController;

final class UrlTest extends TestCase
{
    protected Router $router;

    protected View $view;

    public function testLink(): void
    {
        $this->router->get('home', 'Home');

        $this->assertSame(
            '<a href="/home">Title</a>',
            $this->view->Url->link('Title', [
                'href' => '/home',
            ])
        );
    }

    public function testLinkAttributes(): void
    {
        $this->router->get('home', 'Home');

        $this->assertSame(
            '<a class="test" href="/home">Title</a>',
            $this->view->Url->link('Title', [
                'class' => 'test',
                'href' => '/home',
            ])
        );
    }

    public function testLinkEscape(): void
    {
        $this->router->get('home', 'Home');

        $this->assertSame(
            '<a href="/home">&lt;i&gt;Title&lt;/i&gt;</a>',
            $this->view->Url->link('<i>Title</i>', [
                'href' => '/home',
            ])
        );
    }

    public function testLinkNoEscape(): void
    {
        $this->router->get('home', 'Home');

        $this->assertSame(
            '<a href="/home"><i>Title</i></a>',
            $this->view->Url->link('<i>Title</i>', [
                'href' => '/home',
            ], false)
        );
    }

    public function testPath(): void
    {
        $this->assertSame(
            '/assets/test.txt',
            $this->view->Url->path('/assets/test.txt')
        );
    }

    public function testPathFull(): void
    {
        $this->assertSame(
            'https://test.com/assets/test.txt',
            $this->view->Url->path('/assets/test.txt', true)
        );
    }

    public function testTo(): void
    {
        $this->router->get('home', HomeController::class, as: 'home');

        $this->assertSame(
            '/home',
            $this->view->Url->to('home', full: false)
        );
    }

    public function testToArguments(): void
    {
        $this->router->get('home/{id}', HomeController::class, as: 'home');

        $this->assertSame(
            '/home/1',
            $this->view->Url->to('home', ['id' => 1], full: false)
        );
    }

    public function testToFull(): void
    {
        $this->router->get('home', HomeController::class, as: 'home');

        $this->assertSame(
            'https://test.com/home',
            $this->view->Url->to('home', full: true)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Config::class);
        $container->singleton(TemplateLocator::class);
        $container->singleton(HelperRegistry::class);
        $container->singleton(CellRegistry::class);
        $container->singleton(Router::class);

        $container->use(Config::class)->set('App.baseUri', 'https://test.com/');

        $this->router = $container->use(Router::class);

        $request = $container->build(ServerRequest::class);

        $this->view = $container->build(View::class, ['request' => $request]);
    }
}
