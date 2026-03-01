<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Entity;

use Fyre\ORM\Entity;

trait DirtyTestTrait
{
    public function testCleanDirty(): void
    {
        $entity = new Entity();

        $entity->setDirty('test');
        $entity->clean();

        $this->assertFalse(
            $entity->isDirty('test')
        );
    }

    public function testClearDirty(): void
    {
        $entity = new Entity();

        $entity->setDirty('test');
        $entity->clear(['test']);

        $this->assertFalse(
            $entity->isDirty('test')
        );
    }

    public function testExtractDirty(): void
    {
        $entity = new Entity([
            'test1' => 1,
            'test2' => 2,
            'test3' => 3,
        ]);

        $entity->setDirty('test2');

        $this->assertSame(
            [
                'test2' => 2,
            ],
            $entity->extractDirty()
        );
    }

    public function testExtractDirtyFields(): void
    {
        $entity = new Entity([
            'test1' => 1,
            'test2' => 2,
            'test3' => 3,
        ]);

        $entity->setDirty('test2');

        $this->assertSame(
            [
                'test2' => 2,
            ],
            $entity->extractDirty(['test2', 'test3'])
        );
    }

    public function testIsDirtyFalseSetSameValue(): void
    {
        $entity = new Entity([
            'test' => 2,
        ]);

        $entity->set('test', 2);

        $this->assertFalse(
            $entity->isDirty('test')
        );
    }

    public function testIsDirtyFromSet(): void
    {
        $entity = new Entity();

        $entity->set('test', 2);

        $this->assertTrue(
            $entity->isDirty('test')
        );
    }

    public function testIsDirtyInvalid(): void
    {
        $entity = new Entity();

        $this->assertFalse(
            $entity->isDirty('invalid')
        );
    }

    public function testSetDirty(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->setDirty('test')
        );

        $this->assertTrue(
            $entity->isDirty('test')
        );
    }

    public function testSetDirtyFalse(): void
    {
        $entity = new Entity();

        $entity->set('test', 2);
        $entity->setDirty('test', false);

        $this->assertFalse(
            $entity->isDirty('test')
        );
    }

    public function testUnsetDirty(): void
    {
        $entity = new Entity();

        $entity->setDirty('test');
        $entity->unset('test');

        $this->assertFalse(
            $entity->isDirty('test')
        );
    }
}
