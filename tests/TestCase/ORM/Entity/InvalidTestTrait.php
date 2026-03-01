<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Entity;

use Fyre\ORM\Entity;

trait InvalidTestTrait
{
    public function testCleanInvalid(): void
    {
        $entity = new Entity();

        $entity->setInvalid('test', 1);
        $entity->clean();

        $this->assertNull(
            $entity->getInvalid('test')
        );
    }

    public function testFillInvalid(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->fillInvalid([
                'test' => 1,
            ])
        );

        $this->assertSame(
            1,
            $entity->getInvalid('test')
        );
    }

    public function testFillInvalidNotOverwrite(): void
    {
        $entity = new Entity();

        $entity->setInvalid('test', 1);
        $entity->fillInvalid([
            'test' => 2,
        ]);

        $this->assertSame(
            1,
            $entity->getInvalid('test')
        );
    }

    public function testFillInvalidOverwrite(): void
    {
        $entity = new Entity();

        $entity->setInvalid('test', 1);
        $entity->fillInvalid([
            'test' => 2,
        ], true);

        $this->assertSame(
            2,
            $entity->getInvalid('test')
        );
    }

    public function testGetInvalidArray(): void
    {
        $entity = new Entity();

        $entity->setInvalid('test', 1);

        $this->assertSame(
            [
                'test' => 1,
            ],
            $entity->getInvalid()
        );
    }

    public function testGetInvalidClean(): void
    {
        $entity = new Entity();

        $entity->setInvalid('test', 1);
        $entity->setDirty('test', false);

        $this->assertSame(
            1,
            $entity->getInvalid('test')
        );
    }

    public function testGetInvalidDirty(): void
    {
        $entity = new Entity();

        $entity->setInvalid('test', 1);
        $entity->setDirty('test');

        $this->assertNull(
            $entity->getInvalid('test')
        );
    }

    public function testGetInvalidInvalid(): void
    {
        $entity = new Entity();

        $this->assertNull(
            $entity->getInvalid('invalid')
        );
    }

    public function testSetInvalid(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->setInvalid('test', 1)
        );

        $this->assertSame(
            1,
            $entity->getInvalid('test')
        );
    }

    public function testSetInvalidNotOverwrite(): void
    {
        $entity = new Entity();

        $entity->setInvalid('test', 1);
        $entity->setInvalid('test', 2, false);

        $this->assertSame(
            1,
            $entity->getInvalid('test')
        );
    }

    public function testSetInvalidOverwrite(): void
    {
        $entity = new Entity();

        $entity->setInvalid('test', 1);
        $entity->setInvalid('test', 2);

        $this->assertSame(
            2,
            $entity->getInvalid('test')
        );
    }
}
