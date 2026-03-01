<?php
declare(strict_types=1);

namespace Tests\TestCase\View\View;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Event\EventManager;
use Fyre\Http\ServerRequest;
use Fyre\View\CellRegistry;
use Fyre\View\HelperRegistry;
use Fyre\View\TemplateLocator;
use Fyre\View\View;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class ViewTest extends TestCase
{
    use BlockTestTrait;
    use DataTestTrait;
    use ElementTestTrait;
    use LayoutTestTrait;
    use RenderTestTrait;

    protected TemplateLocator $templateLocator;

    protected View $view;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(View::class)
        );
    }

    public function testGetRequest(): void
    {
        $this->assertInstanceOf(
            ServerRequest::class,
            $this->view->getRequest()
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(View::class)
        );
    }

    public function testPathTrailingSlash(): void
    {
        $this->templateLocator->clear();
        $this->templateLocator->addPath('tests/templates/');

        $this->view->setLayout(null);

        $this->assertSame(
            'Test',
            $this->view->render('test/deep/test')
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
        $container->singleton(EventManager::class);

        $this->templateLocator = $container->use(TemplateLocator::class);
        $this->templateLocator->addPath('tests/templates');

        $request = $container->build(ServerRequest::class);

        $this->view = $container->build(View::class, ['request' => $request]);
    }
}
