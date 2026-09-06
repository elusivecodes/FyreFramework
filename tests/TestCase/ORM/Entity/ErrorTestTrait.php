<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Entity;

use Closure;
use Fyre\ORM\Entity;
use PHPUnit\Framework\Attributes\DataProvider;

trait ErrorTestTrait
{
    /**
     * @return array<string, array{Closure(Entity): array<string, Entity|Entity[]>, string, array<array-key, mixed>}>
     */
    public static function getErrorPathProvider(): array
    {
        return [
            'child errors' => [
                static fn(Entity $child): array => ['child' => $child],
                'child',
                ['test' => ['error']],
            ],
            'child field errors' => [
                static fn(Entity $child): array => ['child' => $child],
                'child.test',
                ['error'],
            ],
            'nested field errors' => [
                static fn(Entity $child): array => ['children' => [$child]],
                'children.0.test',
                ['error'],
            ],
            'nested child errors' => [
                static fn(Entity $child): array => ['children' => [$child]],
                'children',
                [['test' => ['error']]],
            ],
        ];
    }

    public function testCleanErrors(): void
    {
        $entity = new Entity();

        $entity->setError('test', 'error');
        $entity->clean();

        $this->assertArraysAreIdentical(
            [],
            $entity->getError('test')
        );
    }

    public function testGetErrorClean(): void
    {
        $entity = new Entity();

        $entity->setError('test', 'error');
        $entity->setDirty('test', false);

        $this->assertArraysAreIdentical(
            [
                'error',
            ],
            $entity->getError('test')
        );
    }

    public function testGetErrorDirty(): void
    {
        $entity = new Entity();

        $entity->setError('test', 'error');
        $entity->setDirty('test');

        $this->assertArraysAreIdentical(
            [],
            $entity->getError('test')
        );
    }

    public function testGetErrorNestedField(): void
    {
        $child = new Entity();
        $child->setError('test', 'error');

        $parent = new Entity([
            'children' => [$child, null],
        ]);

        $this->assertArraysAreIdentical(
            [
                [
                    'error',
                ],
            ],
            $parent->getError('children.test')
        );
    }

    public function testGetErrorNestedInvalid(): void
    {
        $entity = new Entity();

        $this->assertArraysAreIdentical(
            [],
            $entity->getError('child.test')
        );
    }

    /**
     * @param Closure(Entity): array<string, Entity|Entity[]> $data
     * @param array<array-key, mixed> $expected
     */
    #[DataProvider('getErrorPathProvider')]
    public function testGetErrorPath(Closure $data, string $path, array $expected): void
    {
        $child = new Entity();
        $parent = new Entity($data($child));

        $child->setError('test', 'error');

        $this->assertArraysAreIdentical(
            $expected,
            $parent->getError($path)
        );
    }

    public function testGetErrors(): void
    {
        $entity = new Entity();

        $entity->setError('test', 'error');

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

    public function testHasErrorsWithoutNested(): void
    {
        $child = new Entity();
        $child->setError('test', 'error');

        $parent = new Entity([
            'child' => $child,
        ]);

        $this->assertFalse(
            $parent->hasErrors(false)
        );
    }

    public function testSetError(): void
    {
        $entity = new Entity();

        $this->assertSame(
            $entity,
            $entity->setError('test', 'error')
        );

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
            [
                'error2',
            ],
            $entity->getError('test')
        );
    }
}
