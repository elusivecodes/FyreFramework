<?php
declare(strict_types=1);

namespace Tests\TestCase\Router\Router;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Http\MiddlewareRegistry;
use Fyre\Router\Route;
use Fyre\Router\RouteHandler;
use Fyre\Router\Router;
use Fyre\Router\Routes\ClosureRoute;
use Fyre\Router\Routes\ControllerRoute;
use Fyre\Router\Routes\RedirectRoute;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class RouterTest extends TestCase
{
    use BaseUriTestTrait;
    use ConnectTestTrait;
    use DeleteTestTrait;
    use FindRouteTestTrait;
    use GetTestTrait;
    use MiddlewareTestTrait;
    use PatchTestTrait;
    use PlaceholderTestTrait;
    use PostTestTrait;
    use PrefixTestTrait;
    use PutTestTrait;
    use RedirectTestTrait;
    use UrlTestTrait;

    protected Container $container;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Router::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Route::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(RouteHandler::class)
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Router::class)
        );

        $this->assertContains(
            MacroTrait::class,
            class_uses(ClosureRoute::class)
        );

        $this->assertContains(
            MacroTrait::class,
            class_uses(ControllerRoute::class)
        );

        $this->assertContains(
            MacroTrait::class,
            class_uses(RedirectRoute::class)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(Config::class);
        $this->container->singleton(Router::class);
        $this->container->singleton(MiddlewareRegistry::class);
    }
}
