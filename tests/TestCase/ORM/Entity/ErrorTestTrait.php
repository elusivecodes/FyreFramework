<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Entity;

use Fyre\ORM\Entity;

trait ErrorTestTrait
{
    public function testCleanErrors(): void
    {
        $entity = new Entity();

        $entity->setError('test', 'error');
        $entity->clean();

        $this->assertSame(
            [],
            $entity->getError('test')
        );
    }

    public function testGetErrorChild(): void
    {
        $child = new Entity();
        $parent = new Entity([
            'child' => $child,
        ]);

        $child->setError('test', 'error');

        $this->assertSame(
            [
                'test' => [
                    'error',
                ],
            ],
            $parent->getError('child')
        );
    }

    public function testGetErrorClean(): void
    {
        $entity = new Entity();

        $entity->setError('test', 'error');
        $entity->setDirty('test', false);

        $this->assertSame(
            [
                'error',
            ],
            $entity->getError('test')
        );
    }

    public function testGetErrorDeep(): void
    {
        $child = new Entity();
        $parent = new Entity([
            'child' => $child,
        ]);

        $child->setError('test', 'error');

        $this->assertSame(
            [
                'error',
            ],
            $parent->getError('child.test')
        );
    }

    public function testGetErrorDirty(): void
    {
        $entity = new Entity();

        $entity->setError('test', 'error');
        $entity->setDirty('test');

        $this->assertSame(
            [],
            $entity->getError('test')
        );
    }

    public function testGetErrorNested(): void
    {
        $child = new Entity();
        $parent = new Entity([
            'children' => [$child],
        ]);

        $child->setError('test', 'error');

        $this->assertSame(
            [
                'error',
            ],
            $parent->getError('children.0.test')
        );
    }

    public function testGetErrorNestedChild(): void
    {
        $child = new Entity();
        $parent = new Entity([
            'children' => [$child],
        ]);

        $child->setError('test', 'error');

        $this->assertSame(
            [
                [
                    'test' => [
                        'error',
                    ],
                ],
            ],
            $parent->getError('children')
        );
    }

    public function testGetErrors(): void
    {
        $entity = new Entity();

        $entity->setError('test', 'error');

        $this->assertSame(
            [
                'test' => [
                    'error',
                ],
            ],
            $entity->getErrors()
        );
    }

    public function testGetErrorsDeep(): void
    {
        $child = new Entity();
        $parent = new Entity([
            'child' => $child,
        ]);

        $child->setError('test', 'error');

        $this->assertSame(
            [
                'child' => [
                    'test' => [
                        'error',
                    ],
                ],
            ],
            $parent->getErrors()
        );
    }

    public function testGetErrorsNested(): void
    {
        $child = new Entity();
        $parent = new Entity([
            'children' => [$child],
        ]);

        $child->setError('test', 'error');

        $this->assertSame(
            [
                'children' => [
                    [
                        'test' => [
                            'error',
                        ],
                    ],
                ],
            ],
            $parent->getErrors()
        );
    }

    public function testHasErrors(): void
    {
        $entity = new Entity();

        $entity->setError('test', 'error');

        $this->assertTrue(
            $entity->hasErrors()
        );
    }

    public function testHasErrorsDeep(): void
    {
        $child = new Entity();
        $parent = new Entity([
            'child' => $child,
        ]);

        $child->setError('test', 'error');

        $this->assertTrue(
            $parent->hasErrors()
        );
    }

    public function testHasErrorsFalse(): void
    {
        $entity = new Entity();

        $this->assertFalse(
            $entity->hasErrors()
        );
    }

    public function testHasErrorsNested(): void
    {
        $child = new Entity();
        $parent = new Entity([
            'children' => [$child],
        ]);

        $child->setError('test', 'error');

        $this->assertTrue(
            $parent->hasErrors()
        );
    }

    public function testSetError(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->setError('test', 'error')
        );

        $this->assertSame(
            [
                'error',
            ],
            $entity->getError('test')
        );
    }

    public function testSetErrorArray(): void
    {
        $entity = new Entity();

        $entity->setError('test', [
            'error1',
            'error2',
        ]);

        $this->assertSame(
            [
                'error1',
                'error2',
            ],
            $entity->getError('test')
        );
    }

    public function testSetErrorMerge(): void
    {
        $entity = new Entity();

        $entity->setError('test', 'error1');
        $entity->setError('test', 'error2');

        $this->assertSame(
            [
                'error1',
                'error2',
            ],
            $entity->getError('test')
        );
    }

    public function testSetErrorOverwrite(): void
    {
        $entity = new Entity();

        $entity->setError('test', 'error1');
        $entity->setError('test', 'error2', true);

        $this->assertSame(
            [
                'error2',
            ],
            $entity->getError('test')
        );
    }

    public function testSetErrors(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->setErrors([
                'test' => 'error',
            ])
        );

        $this->assertSame(
            [
                'error',
            ],
            $entity->getError('test')
        );
    }

    public function testSetErrorsMerge(): void
    {
        $entity = new Entity();

        $entity->setErrors([
            'test' => 'error1',
        ]);

        $entity->setErrors([
            'test' => 'error2',
        ]);

        $this->assertSame(
            [
                'error1',
                'error2',
            ],
            $entity->getError('test')
        );
    }

    public function testSetErrorsOverwrite(): void
    {
        $entity = new Entity();

        $entity->setErrors([
            'test' => 'error1',
        ]);

        $entity->setErrors([
            'test' => 'error2',
        ], true);

        $this->assertSame(
            [
                'error2',
            ],
            $entity->getError('test')
        );
    }
}
