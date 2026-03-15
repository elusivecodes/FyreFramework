<?php
declare(strict_types=1);

namespace Tests\TestCase\Core;

use Closure;
use Fyre\Core\Container;
use Fyre\Core\Exceptions\ContainerException;
use Fyre\Core\Exceptions\ContainerNotFoundException;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Core\Container\ArgumentService;
use Tests\Mock\Core\Container\ContainerService;
use Tests\Mock\Core\Container\InnerService;
use Tests\Mock\Core\Container\InvokableClass;
use Tests\Mock\Core\Container\Item;
use Tests\Mock\Core\Container\ItemContext;
use Tests\Mock\Core\Container\ItemService;
use Tests\Mock\Core\Container\OuterService;
use Tests\Mock\Core\Container\Service;

use function class_uses;

final class ContainerTest extends TestCase
{
    protected Container $container;

    public function testBuild(): void
    {
        $service = $this->container->build(Service::class);

        $this->assertInstanceOf(Service::class, $service);
    }

    public function testBuildArguments(): void
    {
        $argumentService = $this->container->build(ArgumentService::class, ['a' => 4, 'b' => 5, 'c' => 6]);

        $this->assertInstanceOf(ArgumentService::class, $argumentService);

        $this->assertSame(
            [4, 5, 6],
            $argumentService->getArguments()
        );
    }

    public function testBuildArgumentsDefaults(): void
    {
        $argumentService = $this->container->build(ArgumentService::class, ['b' => 5]);

        $this->assertInstanceOf(ArgumentService::class, $argumentService);

        $this->assertSame(
            [1, 5, 3],
            $argumentService->getArguments()
        );
    }

    public function testBuildContainerDependency(): void
    {
        $containerService = $this->container->build(ContainerService::class);

        $this->assertInstanceOf(ContainerService::class, $containerService);

        $this->assertSame(
            $this->container,
            $containerService->getContainer()
        );
    }

    public function testBuildContext(): void
    {
        $itemService = $this->container->build(ItemService::class);

        $item = $itemService->getItem();

        $this->assertInstanceOf(
            Item::class,
            $item
        );

        $this->assertSame(
            'test',
            $item->getValue()
        );
    }

    public function testBuildContextFromBinding(): void
    {
        $this->container->bindAttribute(ItemContext::class, function(Container $container): Item {
            $this->assertSame(
                $this->container,
                $container
            );

            return new Item('other');
        });

        $itemService = $this->container->build(ItemService::class);

        $item = $itemService->getItem();

        $this->assertInstanceOf(
            Item::class,
            $item
        );

        $this->assertSame(
            'other',
            $item->getValue()
        );
    }

    public function testBuildDependency(): void
    {
        $outerService = $this->container->build(OuterService::class);

        $this->assertInstanceOf(OuterService::class, $outerService);

        $innerService = $outerService->getInnerService();

        $this->assertInstanceOf(InnerService::class, $innerService);

        $this->assertNotSame(
            $innerService,
            $this->container->use(InnerService::class)
        );
    }

    public function testBuildNotInstantiable(): void
    {
        $this->expectException(ContainerNotFoundException::class);
        $this->expectExceptionMessage('Class `Closure` is not instantiable.');

        $this->container->build(Closure::class);
    }

    public function testBuildSharedDependency(): void
    {
        $this->container->singleton(InnerService::class);

        $outerService = $this->container->build(OuterService::class);

        $this->assertInstanceOf(OuterService::class, $outerService);

        $innerService = $outerService->getInnerService();

        $this->assertInstanceOf(InnerService::class, $innerService);

        $this->assertSame(
            $innerService,
            $this->container->use(InnerService::class)
        );
    }

    public function testCall(): void
    {
        $this->container->singleton(InnerService::class);

        $ran = false;
        $result = $this->container->call(function(Container $container, OuterService $outerService) use (&$ran): int {
            $ran = true;

            $this->assertSame($this->container, $container);

            $this->assertSame(
                $outerService->getInnerService(),
                $this->container->use(InnerService::class)
            );

            return 3;
        });

        $this->assertTrue($ran);

        $this->assertSame(3, $result);
    }

    public function testCallArguments(): void
    {
        $ran = false;
        $result = $this->container->call(static function(int $a = 1, int $b = 2, int $c = 3) use (&$ran): array {
            $ran = true;

            return [$a, $b, $c];
        }, ['a' => 4, 'b' => 5, 'c' => 6]);

        $this->assertTrue($ran);

        $this->assertSame(
            [4, 5, 6],
            $result
        );
    }

    public function testCallArgumentsDependency(): void
    {
        $this->container->singleton(InnerService::class);

        $ran = false;
        $result = $this->container->call(function(Container $container, OuterService $outerService, int $a = 1, int $b = 2, int $c = 3) use (&$ran): array {
            $ran = true;

            $this->assertSame($this->container, $container);

            $this->assertSame(
                $outerService->getInnerService(),
                $this->container->use(InnerService::class)
            );

            return [$a, $b, $c];
        }, ['b' => 5]);

        $this->assertTrue($ran);

        $this->assertSame(
            [1, 5, 3],
            $result
        );
    }

    public function testCallArrayObject(): void
    {
        $result = $this->container->call([new Service(), 'value'], ['a' => 1]);

        $this->assertSame(
            1,
            $result
        );
    }

    public function testCallArrayStatic(): void
    {
        $result = $this->container->call([Service::class, 'staticValue'], ['a' => 1]);

        $this->assertSame(
            1,
            $result
        );
    }

    public function testCallArrayString(): void
    {
        $result = $this->container->call([Service::class, 'value'], ['a' => 1]);

        $this->assertSame(
            1,
            $result
        );
    }

    public function testCallInvalidMethod(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Method name must be a string.');

        $this->container->call([Service::class, 1]);
    }

    public function testCallInvalidTarget(): void
    {
        $this->expectException(ContainerException::class);
        $this->expectExceptionMessage('Callable target must be a class-string or object.');

        $this->container->call([1, 'value']);
    }

    public function testCallInvokableArrayObject(): void
    {
        $result = $this->container->call([new InvokableClass()], ['a' => 1]);

        $this->assertSame(
            1,
            $result
        );
    }

    public function testCallInvokableArrayString(): void
    {
        $result = $this->container->call([InvokableClass::class], ['a' => 1]);

        $this->assertSame(
            1,
            $result
        );
    }

    public function testCallInvokableClass(): void
    {
        $result = $this->container->call(InvokableClass::class, ['a' => 1]);

        $this->assertSame(
            1,
            $result
        );
    }

    public function testCallInvokableObject(): void
    {
        $result = $this->container->call(new InvokableClass(), ['a' => 1]);

        $this->assertSame(
            1,
            $result
        );
    }

    public function testCallString(): void
    {
        $result = $this->container->call(Service::class.'::value', ['a' => 1]);

        $this->assertSame(
            1,
            $result
        );
    }

    public function testCallStringStatic(): void
    {
        $result = $this->container->call(Service::class.'::staticValue', ['a' => 1]);

        $this->assertSame(
            1,
            $result
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Container::class)
        );
    }

    public function testGet(): void
    {
        $service = $this->container->get(Service::class);

        $this->assertInstanceOf(
            Service::class,
            $service
        );
    }

    public function testGlobalInstance(): void
    {
        $container = Container::getInstance();

        $this->assertInstanceOf(
            Container::class,
            $container
        );

        $this->assertSame(
            $container,
            Container::getInstance()
        );

        $container = new Container();

        Container::setInstance($container);

        $this->assertSame(
            $container,
            Container::getInstance()
        );
    }

    public function testHas(): void
    {
        $this->assertTrue(
            $this->container->has(Service::class)
        );
    }

    public function testHasAlias(): void
    {
        $this->container->bind('service', Service::class);

        $this->assertTrue(
            $this->container->has('service')
        );
    }

    public function testHasBoundFactory(): void
    {
        $this->container->bind('service', static fn(): Service => new Service());

        $this->assertTrue(
            $this->container->has('service')
        );
    }

    public function testHasCircularAlias(): void
    {
        $this->container->bind('service1', 'service2');
        $this->container->bind('service2', 'service1');

        $this->assertFalse(
            $this->container->has('service1')
        );
    }

    public function testHasInvalid(): void
    {
        $this->assertFalse(
            $this->container->has('Invalid')
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Container::class)
        );
    }

    public function testUse(): void
    {
        $service = $this->container->use(Service::class);

        $this->assertInstanceOf(Service::class, $service);

        $this->assertNotSame(
            $service,
            $this->container->use(Service::class)
        );
    }

    public function testUseFactory(): void
    {
        $argumentService = new ArgumentService(7, 8, 9);

        $this->assertSame(
            $this->container,
            $this->container->bind(ArgumentService::class, static fn(): ArgumentService => $argumentService)
        );

        $this->assertSame(
            $argumentService,
            $this->container->use(ArgumentService::class)
        );
    }

    public function testUseInstance(): void
    {
        $argumentService = new ArgumentService(7, 8, 9);

        $this->assertSame(
            $argumentService,
            $this->container->instance(ArgumentService::class, $argumentService)
        );

        $this->assertSame(
            $argumentService,
            $this->container->use(ArgumentService::class)
        );
    }

    public function testUseScoped(): void
    {
        $this->assertSame(
            $this->container,
            $this->container->scoped(Service::class)
        );

        $service = $this->container->use(Service::class);

        $this->assertInstanceOf(Service::class, $service);

        $this->assertSame(
            $service,
            $this->container->use(Service::class)
        );

        $this->assertSame(
            $this->container,
            $this->container->clearScoped()
        );

        $this->assertNotSame(
            $service,
            $this->container->use(Service::class)
        );
    }

    public function testUseScopedDependency(): void
    {
        $this->assertSame(
            $this->container,
            $this->container->scoped(InnerService::class)
        );

        $this->assertSame(
            $this->container,
            $this->container->singleton(OuterService::class)
        );

        $outerService = $this->container->use(OuterService::class);

        $this->assertInstanceOf(OuterService::class, $outerService);

        $innerService = $outerService->getInnerService();

        $this->assertInstanceOf(InnerService::class, $innerService);

        $this->assertSame(
            $innerService,
            $this->container->use(InnerService::class)
        );

        $this->assertSame(
            $this->container,
            $this->container->clearScoped()
        );

        $this->assertNotSame(
            $outerService,
            $this->container->use(OuterService::class)
        );

        $this->assertNotSame(
            $innerService,
            $this->container->use(InnerService::class)
        );
    }

    public function testUseShared(): void
    {
        $this->assertSame(
            $this->container,
            $this->container->singleton(Service::class)
        );

        $service = $this->container->use(Service::class);

        $this->assertInstanceOf(Service::class, $service);

        $this->assertSame(
            $service,
            $this->container->use(Service::class)
        );
    }

    public function testUseUnscoped(): void
    {
        $this->assertSame(
            $this->container,
            $this->container->scoped(Service::class)
        );

        $service = $this->container->use(Service::class);

        $this->assertInstanceOf(Service::class, $service);

        $this->assertSame(
            $service,
            $this->container->use(Service::class)
        );

        $this->assertSame(
            $this->container,
            $this->container->unscoped(Service::class)
        );

        $this->assertSame(
            $this->container,
            $this->container->clearScoped()
        );

        $this->assertSame(
            $service,
            $this->container->use(Service::class)
        );
    }

    public function testUseUnset(): void
    {
        $this->assertSame(
            $this->container,
            $this->container->singleton(Service::class)
        );

        $service = $this->container->use(Service::class);

        $this->assertInstanceOf(Service::class, $service);

        $this->assertSame(
            $service,
            $this->container->use(Service::class)
        );

        $this->assertSame(
            $this->container,
            $this->container->unset(Service::class)
        );

        $this->assertNotSame(
            $service,
            $this->container->use(Service::class)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
    }
}
