<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Entity;

use Fyre\ORM\Entity;

trait OriginalTestTrait
{
    public function testCleanOriginal(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $entity->set('test', 2);
        $entity->clean();

        $this->assertSame(
            2,
            $entity->getOriginal('test')
        );
    }

    public function testClearOriginal(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $entity->set('test', 2);
        $entity->clear(['test']);

        $this->assertNull(
            $entity->getOriginal('test')
        );
    }

    public function testExtractOriginal(): void
    {
        $entity = new Entity([
            'test1' => 1,
            'test2' => 2,
            'test3' => 3,
        ]);

        $entity->set('test2', 4);

        $this->assertSame(
            [
                'test2' => 2,
                'test3' => 3,
            ],
            $entity->extractOriginal(['test2', 'test3'])
        );
    }

    public function testExtractOriginalChanged(): void
    {
        $entity = new Entity([
            'test1' => 1,
            'test2' => 2,
            'test3' => 3,
        ]);

        $entity->set('test2', 4);

        $this->assertSame(
            [
                'test2' => 2,
            ],
            $entity->extractOriginalChanged(['test1', 'test2', 'test3'])
        );
    }

    public function testExtractOriginalChangedInvalid(): void
    {
        $entity = new Entity();

        $this->assertSame(
            [],
            $entity->extractOriginalChanged(['invalid'])
        );
    }

    public function testExtractOriginalFallback(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $this->assertSame(
            [
                'test' => 1,
            ],
            $entity->extractOriginal(['test'])
        );
    }

    public function testExtractOriginalInvalid(): void
    {
        $entity = new Entity();

        $this->assertSame(
            [],
            $entity->extractOriginal(['invalid'])
        );
    }

    public function testGetOriginal(): void
    {
        $entity = new Entity([
            'test' => 1,
        ], clean: true);

        $entity->set('test', 2);

        $this->assertSame(
            1,
            $entity->getOriginal('test')
        );
    }

    public function testGetOriginalAfterUnset(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $entity->set('test', 2);
        $entity->unset('test');

        $this->assertNull(
            $entity->getOriginal('test')
        );
    }

    public function testGetOriginalFallback(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $this->assertSame(
            1,
            $entity->getOriginal('test')
        );
    }

    public function testGetOriginalFields(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $this->assertSame(
            [
                'test',
            ],
            $entity->getOriginalFields()
        );
    }

    public function testGetOriginalFromSet(): void
    {
        $entity = new Entity();

        $entity->set('test', 1);
        $entity->set('test', 2);

        $this->assertSame(
            2,
            $entity->getOriginal('test')
        );
    }

    public function testGetOriginalInvalid(): void
    {
        $entity = new Entity();

        $this->assertNull(
            $entity->getOriginal('invalid')
        );
    }

    public function testGetOriginalMultipleSet(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $entity->set('test', 2);
        $entity->set('test', 3);

        $this->assertSame(
            1,
            $entity->getOriginal('test')
        );
    }

    public function testGetOriginalValues(): void
    {
        $entity = new Entity([
            'test1' => 1,
            'test2' => 2,
            'test3' => 3,
        ]);

        $entity->set('test2', 4);
        $entity->set('test4', 4);

        $this->assertSame(
            [
                'test1' => 1,
                'test2' => 2,
                'test3' => 3,
            ],
            $entity->getOriginalValues()
        );
    }

    public function testHasOriginal(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);
        $entity->set('test', 2);

        $this->assertTrue(
            $entity->hasOriginal('test')
        );
    }

    public function testHasOriginalFalse(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $this->assertFalse(
            $entity->hasOriginal('test')
        );
    }

    public function testIsOriginalField(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $this->assertTrue(
            $entity->isOriginalField('test')
        );
    }

    public function testIsOriginalFieldFalse(): void
    {
        $entity = new Entity();
        $entity->set('test', 1);

        $this->assertFalse(
            $entity->isOriginalField('test')
        );
    }

    public function testSetOriginalFields(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->setOriginalFields(['test'])
        );

        $this->assertSame(
            ['test'],
            $entity->getOriginalFields()
        );
    }

    public function testUnsetOriginal(): void
    {
        $entity = new Entity([
            'test' => 1,
        ]);

        $entity->set('test', 2);
        $entity->unset('test');

        $this->assertNull(
            $entity->getOriginal('test')
        );
    }
}
