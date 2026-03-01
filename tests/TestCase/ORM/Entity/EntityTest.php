<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Entity;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\ORM\Entity;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Entities\MockEntity;

use function class_uses;

final class EntityTest extends TestCase
{
    use AccessTestTrait;
    use DirtyTestTrait;
    use ErrorTestTrait;
    use FieldTestTrait;
    use HiddenTestTrait;
    use InvalidTestTrait;
    use MagicTestTrait;
    use MutationTestTrait;
    use OriginalTestTrait;
    use TemporaryTestTrait;
    use VirtualTestTrait;

    public function testClean(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->clean()
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Entity::class)
        );
    }

    public function testEntityData(): void
    {
        $entity = new Entity([
            'a' => 1,
        ]);

        $this->assertSame(
            1,
            $entity->get('a')
        );

        $this->assertNull(
            $entity->getSource()
        );

        $this->assertTrue(
            $entity->isNew()
        );

        $this->assertFalse(
            $entity->isDirty()
        );
    }

    public function testEntityNotClean(): void
    {
        $entity = new Entity(['a' => 1], clean: false);

        $this->assertTrue(
            $entity->isDirty()
        );
    }

    public function testEntityNotMutate(): void
    {
        $entity = new MockEntity([
            'integer' => 2.5,
        ], mutate: false);

        $this->assertSame(
            2.5,
            $entity->get('integer')
        );
    }

    public function testEntityNotNew(): void
    {
        $entity = new Entity(new: false);

        $this->assertFalse(
            $entity->isNew()
        );
    }

    public function testEntitySetNew(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->setNew(false)
        );

        $this->assertFalse(
            $entity->isNew()
        );
    }

    public function testEntitySetSource(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->setSource('test')
        );

        $this->assertSame(
            'test',
            $entity->getSource()
        );
    }

    public function testEntitySource(): void
    {
        $entity = new Entity(source: 'test');

        $this->assertSame(
            'test',
            $entity->getSource()
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Entity::class)
        );
    }
}
