<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Fixture;

use Fyre\Core\Traits\DebugTrait;
use Fyre\TestSuite\Fixture\FixtureRegistry;
use Fyre\TestSuite\TestCase;

use function class_uses;

class FixtureRegistryTest extends TestCase
{
    use MysqlConnectionTrait;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(FixtureRegistry::class)
        );
    }

    public function testGetNamespaces(): void
    {
        $this->assertSame(
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
}
