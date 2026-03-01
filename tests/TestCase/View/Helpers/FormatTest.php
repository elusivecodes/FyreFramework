<?php
declare(strict_types=1);

namespace Tests\Helpers;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\DB\TypeParser;
use Fyre\Http\ServerRequest;
use Fyre\Utility\Formatter;
use Fyre\View\CellRegistry;
use Fyre\View\HelperRegistry;
use Fyre\View\TemplateLocator;
use Fyre\View\View;
use Override;
use PHPUnit\Framework\TestCase;

final class FormatTest extends TestCase
{
    protected View $view;

    public function testCurrency(): void
    {
        $this->assertSame(
            '$1,234.00',
            $this->view->Format->currency(1234)
        );
    }

    public function testNumber(): void
    {
        $this->assertSame(
            '1,234',
            $this->view->Format->number(1234)
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
        $container->singleton(TypeParser::class);
        $container->singleton(Formatter::class);

        $container->use(Config::class)->set('App.defaultLocale', 'en-US');

        $request = $container->build(ServerRequest::class);

        $this->view = $container->build(View::class, ['request' => $request]);
    }
}
