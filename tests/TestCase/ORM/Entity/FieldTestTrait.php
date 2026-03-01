<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Entity;

use Fyre\ORM\Entity;

trait FieldTestTrait
{
    public function testClear(): void
    {
        $entity = new Entity([
            'test1' => 2,
            'test2' => 3,
        ]);

        $this->assertSame(
            $entity,
            $entity->clear(['test1'])
        );

        $this->assertNull(
            $entity->get('test1')
        );

        $this->assertSame(
            3,
            $entity->get('test2')
        );
    }

    public function testExtract(): void
    {
        $entity = new Entity([
            'test1' => 1,
            'test2' => 2,
            'test3' => 3,
        ]);

        $this->assertSame(
            [
                'test2' => 2,
                'test3' => 3,
            ],
            $entity->extract(['test2', 'test3'])
        );
    }

    public function testExtractInvalid(): void
    {
        $entity = new Entity();

        $this->assertSame(
            [
                'invalid' => null,
            ],
            $entity->extract(['invalid'])
        );
    }

    public function testFill(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->fill([
                'test1' => 2,
                'test2' => 3,
            ])
        );

        $this->assertSame(
            2,
            $entity->get('test1')
        );

        $this->assertSame(
            3,
            $entity->get('test2')
        );
    }

    public function testFillOriginal(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->fill([
                'test1' => 2,
                'test2' => 3,
            ], original: true)
        );

        $this->assertSame(
            [
                'test1',
                'test2',
            ],
            $entity->getOriginalFields()
        );
    }

    public function testGetInvalid(): void
    {
        $entity = new Entity();

        $this->assertNull(
            $entity->get('invalid')
        );
    }

    public function testHas(): void
    {
        $entity = new Entity([
            'test' => 2,
        ]);

        $this->assertTrue(
            $entity->has('test')
        );
    }

    public function testHasEmpty(): void
    {
        $entity = new Entity([
            'test' => '',
        ]);

        $this->assertTrue(
            $entity->has('test')
        );
    }

    public function testHasInvalid(): void
    {
        $entity = new Entity();

        $this->assertFalse(
            $entity->has('invalid')
        );
    }

    public function testHasValue(): void
    {
        $entity = new Entity([
            'test' => 2,
        ]);

        $this->assertTrue(
            $entity->hasValue('test')
        );
    }

    public function testHasValueEmpty(): void
    {
        $entity = new Entity([
            'test' => '',
        ]);

        $this->assertFalse(
            $entity->hasValue('test')
        );
    }

    public function testHasValueInvalid(): void
    {
        $entity = new Entity();

        $this->assertFalse(
            $entity->hasValue('invalid')
        );
    }

    public function testIsEmpty(): void
    {
        $entity = new Entity([
            'test' => 2,
        ]);

        $this->assertFalse(
            $entity->isEmpty()
        );
    }

    public function testIsEmptyEmpty(): void
    {
        $entity = new Entity([
            'test' => '',
        ]);

        $this->assertTrue(
            $entity->isEmpty()
        );
    }

    public function testSet(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->set('test', 2)
        );

        $this->assertSame(
            2,
            $entity->get('test')
        );
    }

    public function testSetOriginal(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->set('test', 2, original: true)
        );

        $this->assertSame(
            [
                'test',
            ],
            $entity->getOriginalFields()
        );
    }

    public function testUnset(): void
    {
        $entity = new Entity([
            'test' => 2,
        ]);

        $this->assertSame(
            $entity,
            $entity->unset('test')
        );

        $this->assertNull(
            $entity->get('test')
        );
    }
}
