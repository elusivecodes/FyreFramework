<?php
declare(strict_types=1);

namespace Tests\Helpers;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\ServerRequest;
use Fyre\View\CellRegistry;
use Fyre\View\Helper;
use Fyre\View\HelperRegistry;
use Fyre\View\TemplateLocator;
use Fyre\View\View;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Helpers\TestHelper;

use function class_uses;

final class HelperTest extends TestCase
{
    protected View $view;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Helper::class)
        );
    }

    public function testGetConfig(): void
    {
        $this->view->loadHelper('Test', [
            'value' => 1,
        ]);

        $helper = $this->view->__get('Test');

        $this->assertInstanceOf(
            TestHelper::class,
            $helper
        );

        $this->assertArraysAreIdentical(
            [
                'value' => 1,
            ],
            $helper->getConfig()
        );
    }

    public function testGetView(): void
    {
        $helper = $this->view->__get('Test');

        $this->assertInstanceOf(
            TestHelper::class,
            $helper
        );

        $this->assertInstanceOf(
            View::class,
            $helper->getView()
        );
    }

    public function testHelper(): void
    {
        $this->view->setLayout(null);

        $this->assertSame(
            'test',
            $this->view->render('test/helper')
        );
    }

    public function testLoadHelperInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Helper `Invalid` could not be found.');

        $this->view->loadHelper('Invalid');
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Config::class);
        $container->singleton(TemplateLocator::class);
        $container->singleton(HelperRegistry::class);
        $container->singleton(CellRegistry::class);

        $container->use(HelperRegistry::class)->addNamespace('\Tests\Mock\Helpers');
        $container->use(TemplateLocator::class)->addPath('tests/templates');

        $request = $container->build(ServerRequest::class);

        $this->view = $container->build(View::class, ['request' => $request]);
    }
}
