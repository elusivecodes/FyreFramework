<?php
declare(strict_types=1);

namespace Tests\TestCase\Core;

use Fyre\Core\Loader;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\Path;
use Override;
use PHPUnit\Framework\TestCase;
use TestClass;

use function class_uses;

final class LoaderTest extends TestCase
{
    protected Loader $loader;

    public function testClassMap(): void
    {
        $this->assertSame(
            $this->loader,
            $this->loader->addClassMap([
                'TestClass' => 'tests/Mock/Core/Loader/TestClass.php',
            ])
        );

        $this->assertTrue(
            TestClass::test()
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Loader::class)
        );
    }

    public function testGetClassMap(): void
    {
        $this->loader->addClassMap([
            'Test\Example' => 'other/classes/Example.php',
            'Test\Deep\Another' => 'files/Deep/Another.php',
        ]);

        $this->assertSame(
            [
                'Test\Example' => Path::resolve('other/classes/Example.php'),
                'Test\Deep\Another' => Path::resolve('files/Deep/Another.php'),
            ],
            $this->loader->getClassMap()
        );
    }

    public function testGetNamespace(): void
    {
        $this->assertSame(
            $this->loader,
            $this->loader->addNamespaces([
                'Test' => 'tests/',
                'Demo' => 'tests/Mock/Core/Loader/Demo',
            ])
        );

        $this->assertSame(
            [
                Path::resolve('tests/Mock/Core/Loader/Demo'),
            ],
            $this->loader->getNamespace('Demo')
        );
    }

    public function testGetNamespaceInvalid(): void
    {
        $this->assertSame(
            [],
            $this->loader->getNamespace('Demo')
        );
    }

    public function testGetNamespacePaths(): void
    {
        $this->loader->addClassMap([
            'Test\Example' => 'other/classes/Example.php',
            'Test\Deep\Another' => 'files/Deep/Another.php',
        ]);

        $this->loader->addNamespaces([
            'Test' => 'tests/',
        ]);

        $this->assertSame(
            [
                Path::resolve('tests'),
                Path::resolve('other/classes'),
                Path::resolve('files'),
            ],
            $this->loader->getNamespacePaths('Test')
        );
    }

    public function testGetNamespaces(): void
    {
        $this->loader->addNamespaces([
            'Test' => 'tests/',
            'Demo' => 'tests/Mock/Core/Loader/Demo',
        ]);

        $this->assertSame(
            [
                'Test\\' => [
                    Path::resolve('tests'),
                ],
                'Demo\\' => [
                    Path::resolve('tests/Mock/Core/Loader/Demo'),
                ],
            ],
            $this->loader->getNamespaces()
        );
    }

    public function testHasNamespace(): void
    {
        $this->loader->addNamespaces([
            'Demo' => 'tests/Mock/Core/Loader/Demo',
        ]);

        $this->assertTrue(
            $this->loader->hasNamespace('Demo')
        );
    }

    public function testHasNamespaceInvalid(): void
    {
        $this->assertFalse(
            $this->loader->hasNamespace('Demo')
        );
    }

    public function testLoadComposer(): void
    {
        $this->loader->loadComposer('tests/autoload.php');

        $this->assertSame(
            [
                Path::resolve('src'),
            ],
            $this->loader->getNamespace('Fyre')
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Loader::class)
        );
    }

    public function testNamespace(): void
    {
        $this->loader->addNamespaces([
            'Demo' => 'tests/Mock/Core/Loader/Demo',
        ]);

        $this->assertTrue(
            \Demo\TestClass::test()
        );
    }

    public function testNamespaceArray(): void
    {
        $this->loader->addNamespaces([
            'Demo' => [
                'tests/Mock/Core/Loader/Demo',
            ],
        ]);

        $this->assertTrue(
            \Demo\TestClass::test()
        );
    }

    public function testNamespaceDeep(): void
    {
        $this->loader->addNamespaces([
            'Demo' => 'tests/Mock/Core/Loader/Demo',
        ]);

        $this->assertTrue(
            \Demo\Deep\TestClass::test()
        );
    }

    public function testNamespaceTrailingSlash(): void
    {
        $this->loader->addNamespaces([
            'Demo' => 'tests/Mock/Core/Loader/Demo/',
        ]);

        $this->assertTrue(
            \Demo\TestClass::test()
        );
    }

    public function testRemoveClass(): void
    {
        $this->loader->addClassMap([
            'Test\Example' => 'other/classes/Example.php',
            'Test\Deep\Another' => 'files/Deep/Another.php',
        ]);

        $this->assertSame(
            $this->loader,
            $this->loader->removeClass('Test\Example')
        );

        $this->assertSame(
            [
                'Test\Deep\Another' => Path::resolve('files/Deep/Another.php'),
            ],
            $this->loader->getClassMap()
        );
    }

    public function testRemoveClassInvalid(): void
    {
        $this->assertSame(
            $this->loader,
            $this->loader->removeClass('Test')
        );
    }

    public function testRemoveNamespace(): void
    {
        $this->loader->addNamespaces([
            'Demo' => 'tests/Mock/Core/Loader/Demo',
        ]);

        $this->assertSame(
            $this->loader,
            $this->loader->removeNamespace('Demo')
        );

        $this->assertFalse(
            $this->loader->hasNamespace('Demo')
        );
    }

    public function testRemoveNamespaceInvalid(): void
    {
        $this->assertSame(
            $this->loader,
            $this->loader->removeNamespace('Demo')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->loader = new Loader();
        $this->loader->register();
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->loader->unregister();
    }
}
