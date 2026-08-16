<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Entity;

use Fyre\ORM\Entity;

trait TemporaryTestTrait
{
    public function testCleanTemporaryFields(): void
    {
        $entity = new Entity();

        $entity->setTemporaryFields([
            'test',
        ]);
        $entity->clean();

        $this->assertArraysAreIdentical(
            [],
            $entity->getTemporaryFields()
        );
    }

    public function testClearTemporaryFields(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $entity->set('test', 2, temporary: true);
        $entity->clearTemporaryFields();

        $this->assertSame(
            1,
            $entity->get('test')
        );

        $this->assertFalse(
            $entity->hasOriginal('test')
        );

        $this->assertArraysAreIdentical(
            [],
            $entity->getTemporaryFields()
        );
    }

    public function testClearTemporaryFieldsNew(): void
    {
        $entity = new Entity();

        $entity->set('test', 1, temporary: true);
        $entity->clearTemporaryFields();

        $this->assertFalse(
            $entity->has('test')
        );

        $this->assertFalse(
            $entity->hasOriginal('test')
        );

        $this->assertArraysAreIdentical(
            [],
            $entity->getTemporaryFields()
        );
    }

    public function testSetTemporaryFields(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->setTemporaryFields([
                'test',
            ])
        );

        $this->assertArraysAreIdentical(
            [
                'test',
            ],
            $entity->getTemporaryFields()
        );
    }

    public function testSetTemporaryFieldsOverwrite(): void
    {
        $entity = new Entity();
        $entity->setTemporaryFields(['test1']);

        $entity->setTemporaryFields(['test2'], true);

        $this->assertArraysAreIdentical(
            ['test2'],
            $entity->getTemporaryFields()
        );
    }
}
