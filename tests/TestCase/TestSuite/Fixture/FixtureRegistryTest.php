<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Fixture;

use Fyre\Core\Traits\DebugTrait;
use Fyre\TestSuite\Fixture\FixtureRegistry;
use Fyre\TestSuite\TestCase;
use InvalidArgumentException;
use Tests\Mock\Fixtures\ItemsFixture;

use function class_uses;

final class FixtureRegistryTest extends TestCase
{
    use MysqlConnectionTrait;

    public function testBuild(): void
    {
        $this->fixtureRegistry->unload('Items');

        $fixture = $this->fixtureRegistry->build('Items');

        $this->assertInstanceOf(
            ItemsFixture::class,
            $fixture
        );

        $this->assertFalse(
            $this->fixtureRegistry->isLoaded('Items')
        );
    }

    public function testBuildInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Fixture `Invalid` does not exist.');

        $this->fixtureRegistry->build('Invalid');
    }

    public function testClear(): void
    {
        $this->assertTrue(
            $this->fixtureRegistry->isLoaded('Items')
        );

        $this->fixtureRegistry->clear();

        $this->assertArraysAreIdentical(
            [],
            $this->fixtureRegistry->getNamespaces()
        );

        $this->assertFalse(
            $this->fixtureRegistry->isLoaded('Items')
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(FixtureRegistry::class)
        );
    }

    public function testGetNamespaces(): void
    {
        $this->assertArraysAreIdentical(
            [
                'Tests\Mock\Fixtures\\',
            ],
            $this->fixtureRegistry->getNamespaces()
        );
    }

    public function testHasNamespace(): void
    {
        $this->assertTrue(
            $this->fixtureRegistry->hasNamespace('Tests\Mock\Fixtures')
        );
    }

    public function testHasNamespaceInvalid(): void
    {
        $this->assertFalse(
            $this->fixtureRegistry->hasNamespace('Tests\Invalid\Model')
        );
    }

    public function testIsLoaded(): void
    {
        $this->assertTrue(
            $this->fixtureRegistry->isLoaded('Items')
        );
    }

    public function testIsLoadedInvalid(): void
    {
        $this->assertFalse(
            $this->fixtureRegistry->isLoaded('Invalid')
        );
    }

    public function testRemoveNamespace(): void
    {
        $this->assertSame(
            $this->fixtureRegistry,
            $this->fixtureRegistry->removeNamespace('Tests\Mock\Fixtures')
        );

        $this->assertFalse(
            $this->fixtureRegistry->hasNamespace('Tests\Mock\Fixtures')
        );
    }

    public function testRemoveNamespaceInvalid(): void
    {
        $this->assertSame(
            $this->fixtureRegistry,
            $this->fixtureRegistry->removeNamespace('Tests\Invalid\Model')
        );
    }

    public function testUnload(): void
    {
        $this->assertSame(
            $this->fixtureRegistry,
            $this->fixtureRegistry->unload('Items')
        );

        $this->assertFalse(
            $this->fixtureRegistry->isLoaded('Items')
        );
    }

    public function testUnloadInvalid(): void
    {
        $this->assertSame(
            $this->fixtureRegistry,
            $this->fixtureRegistry->unload('Invalid')
        );
    }

    public function testUse(): void
    {
        $this->fixtureRegistry->unload('Items');

        $fixture = $this->fixtureRegistry->use('Items');

        $this->assertInstanceOf(
            ItemsFixture::class,
            $fixture
        );

        $this->assertTrue(
            $this->fixtureRegistry->isLoaded('Items')
        );
    }

    public function testUseShared(): void
    {
        $this->assertSame(
            $this->fixtureRegistry->use('Items'),
            $this->fixtureRegistry->use('Items')
        );
    }

    public function testUseUnloadRebuilds(): void
    {
        $fixture = $this->fixtureRegistry->use('Items');

        $this->fixtureRegistry->unload('Items');

        $this->assertNotSame(
            $fixture,
            $this->fixtureRegistry->use('Items')
        );
    }
}
