<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM;

use Fyre\Core\Traits\DebugTrait;
use Fyre\ORM\Entity;
use Fyre\ORM\EntityLocator;
use Fyre\Utility\Inflector;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Entities\MockEntity;

use function class_uses;

final class EntityLocatorTest extends TestCase
{
    protected EntityLocator $locator;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(EntityLocator::class)
        );
    }

    public function testFind(): void
    {
        $this->assertSame(
            MockEntity::class,
            $this->locator->find('MockEntity')
        );
    }

    public function testFindAlias(): void
    {
        $this->assertSame(
            'MockEntities',
            $this->locator->findAlias(MockEntity::class)
        );
    }

    public function testFindInvalid(): void
    {
        $this->assertSame(
            Entity::class,
            $this->locator->find('Invalid')
        );
    }

    public function testFindPlural(): void
    {
        $this->assertSame(
            MockEntity::class,
            $this->locator->find('MockEntities')
        );
    }

    public function testGetDefaultEntityClass(): void
    {
        $this->assertSame(
            Entity::class,
            $this->locator->getDefaultEntityClass()
        );
    }

    public function testGetNamespaces(): void
    {
        $this->assertSame(
            [
                'Tests\Mock\Entities\\',
            ],
            $this->locator->getNamespaces()
        );
    }

    public function testHasNamespace(): void
    {
        $this->assertTrue(
            $this->locator->hasNamespace('Tests\Mock\Entities')
        );
    }

    public function testHasNamespaceInvalid(): void
    {
        $this->assertFalse(
            $this->locator->hasNamespace('Tests\Invalid')
        );
    }

    public function testMap(): void
    {
        $this->assertSame(
            $this->locator,
            $this->locator->map('Test', MockEntity::class)
        );

        $this->assertSame(
            MockEntity::class,
            $this->locator->find('Test')
        );

        $this->assertSame(
            'Test',
            $this->locator->findAlias(MockEntity::class)
        );
    }

    public function testRemoveNamespace(): void
    {
        $this->assertSame(
            $this->locator,
            $this->locator->removeNamespace('Tests\Mock\Entities')
        );

        $this->assertFalse(
            $this->locator->hasNamespace('Tests\Mock\Entities')
        );
    }

    public function testRemoveNamespaceInvalid(): void
    {
        $this->assertSame(
            $this->locator,
            $this->locator->removeNamespace('Tests\Invalid')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $inflector = new Inflector();

        $this->locator = new EntityLocator($inflector);
        $this->locator->addNamespace('Tests\Mock\Entities');
    }
}
