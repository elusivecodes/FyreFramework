<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Query;
use Fyre\ORM\Entity;
use Fyre\ORM\Exceptions\OrmException;
use Tests\Mock\Entities\Item;
use Tests\Mock\Entities\Post;
use Tests\Mock\Entities\User;

use function array_map;
use function range;

trait QueryTestTrait
{
    public function testDelete(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item = $Items->newEntity([
            'name' => 'Test',
        ]);

        $this->assertTrue(
            $Items->save($item)
        );

        $this->assertTrue(
            $Items->delete($item)
        );

        $this->assertSame(
            0,
            $Items->find()->count()
        );
    }

    public function testDeleteIncompleteCompositePrimaryKey(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessageIs('Primary key values for model `CompositeItems` must not be null or missing.');

        $CompositeItems = $this->modelRegistry->use('CompositeItems');
        $item = $CompositeItems->newEntity([
            'tenant_id' => 1,
        ], validate: false, new: false);

        $CompositeItems->delete($item);
    }

    public function testDeleteMany(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $this->assertTrue(
            $Items->deleteMany($items)
        );

        $this->assertSame(
            0,
            $Items->find()->count()
        );
    }

    public function testDeleteManyEmpty(): void
    {
        $this->assertTrue(
            $this->modelRegistry->use('Items')->deleteMany([])
        );
    }

    public function testDeleteManyKeyedSingle(): void
    {
        $Items = $this->modelRegistry->use('Items');

        foreach ([1, 'item'] as $key) {
            $item = $Items->newEntity([
                'name' => 'Test',
            ]);

            $this->assertTrue(
                $Items->save($item)
            );

            $this->assertTrue(
                $Items->deleteMany([$key => $item])
            );

            $this->assertSame(
                0,
                $Items->find()->count()
            );
        }
    }

    public function testExists(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item = $Items->newEntity([
            'name' => 'Test',
        ]);

        $this->assertTrue(
            $Items->save($item)
        );

        $this->assertTrue(
            $Items->exists(['name' => 'Test'])
        );
    }

    public function testExistsNotExists(): void
    {
        $this->assertFalse(
            $this->modelRegistry->use('Items')->exists(['name' => 'Test'])
        );
    }

    public function testFindAutoFields(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item = $Items->newEntity([
            'name' => 'Test',
        ]);

        $this->assertTrue(
            $Items->save($item)
        );

        $item = $Items->get(1, autoFields: false);

        $this->assertInstanceOf(
            Item::class,
            $item
        );

        $this->assertArraysAreIdentical(
            [
                'id' => 1,
            ],
            $item->toArray()
        );
    }

    public function testFindCountTriggersBeforeFindOnce(): void
    {
        $count = 0;
        $Items = $this->modelRegistry->use('Items');

        $Items->getEventManager()->on('ORM.beforeFind', static function() use (&$count): void {
            $count++;
        });

        $Items->find()->count();

        $this->assertSame(
            1,
            $count
        );
    }

    public function testGet(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $item = $Items->get(
            2,
            conditions: static fn(Query $query): ConditionExpression => $query->expr()
                ->eq('Items.name', 'Test 2')
        );

        $this->assertInstanceOf(
            Item::class,
            $item
        );

        $this->assertSame(
            2,
            $item->id
        );
    }

    public function testGetIncompleteCompositePrimaryKey(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessageIs('Primary key values for model `CompositeItems` must not be null or missing.');

        $this->modelRegistry->use('CompositeItems')->get([1]);
    }

    public function testGetInvalid(): void
    {
        $this->assertNull(
            $this->modelRegistry->use('Items')->get(1)
        );
    }

    public function testInsert(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item = $Items->newEntity([
            'name' => 'Test',
        ]);

        $this->assertTrue(
            $Items->save($item)
        );

        $this->assertSame(
            1,
            $item->id
        );

        $this->assertFalse(
            $item->isNew()
        );

        $this->assertFalse(
            $item->isDirty()
        );
    }

    public function testInsertMany(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $this->assertArraysAreIdentical(
            [1, 2],
            array_map(
                static fn(Item $item): int|null => $item->id,
                $items
            )
        );

        $this->assertFalse(
            $items[0]->isNew()
        );

        $this->assertFalse(
            $items[1]->isNew()
        );

        $this->assertFalse(
            $items[0]->isDirty()
        );

        $this->assertFalse(
            $items[1]->isDirty()
        );
    }

    public function testInsertManyBatch(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $data = [];

        for ($i = 0; $i < 1000; $i++) {
            $data[] = [
                'name' => 'Test '.($i + 1),
            ];
        }

        $items = $Items->newEntities($data);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $this->assertArraysAreIdentical(
            range(1, 1000),
            array_map(
                static fn(Item $item): int|null => $item->id,
                $items
            )
        );
    }

    public function testInsertManyChecksExactCompositePrimaryKeys(): void
    {
        $CompositeItems = $this->modelRegistry->use('CompositeItems');

        $CompositeItems->insertQuery()
            ->values([[
                'tenant_id' => 1,
                'id' => 1,
                'name' => 'Existing',
            ]])
            ->execute();

        $items = $CompositeItems->newEntities([
            [
                'tenant_id' => 1,
                'id' => 1,
                'name' => 'Updated',
            ],
            [
                'tenant_id' => 1,
                'id' => 2,
                'name' => 'New',
            ],
        ], validate: false);

        $this->assertTrue(
            $CompositeItems->saveMany($items)
        );

        $this->assertArraysAreIdentical(
            [
                [
                    'tenant_id' => 1,
                    'id' => 1,
                    'name' => 'Updated',
                ],
                [
                    'tenant_id' => 1,
                    'id' => 2,
                    'name' => 'New',
                ],
            ],
            array_map(
                static fn(Entity $item): array => $item->toArray(),
                $CompositeItems->find(orderBy: [
                    'tenant_id' => 'ASC',
                    'id' => 'ASC',
                ])->toArray()
            )
        );
    }

    public function testResolveRouteBinding(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $item = $Items->resolveRouteBinding(2, 'id');

        $this->assertInstanceOf(
            Item::class,
            $item
        );

        $this->assertSame(
            2,
            $item->id
        );
    }

    public function testResolveRouteBindingInvalid(): void
    {
        $this->assertNull(
            $this->modelRegistry->use('Items')->resolveRouteBinding(1, 'id')
        );
    }

    public function testResolveRouteBindingParent(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $Posts = $this->modelRegistry->use('Posts');

        $user = $Users->newEntity([
            'name' => 'Test',
            'posts' => [
                [
                    'title' => 'Test 1',
                    'content' => 'This is the content.',
                ],
                [
                    'title' => 'Test 2',
                    'content' => 'This is the content.',
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $user = $Users->resolveRouteBinding(1, 'id');

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $item = $Posts->resolveRouteBinding(2, 'id', $user);

        $this->assertInstanceOf(
            Post::class,
            $item
        );

        $this->assertSame(
            2,
            $item->id
        );
    }

    public function testResolveRouteBindingParentInvalid(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $Posts = $this->modelRegistry->use('Posts');

        $user = $Users->newEntity([
            'name' => 'Test',
            'posts' => [
                [
                    'title' => 'Test 1',
                    'content' => 'This is the content.',
                ],
                [
                    'title' => 'Test 2',
                    'content' => 'This is the content.',
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $user = $Users->newEntity([
            'name' => 'Test 2',
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $user = $Users->resolveRouteBinding(2, 'id');
        $item = $Posts->resolveRouteBinding(2, 'id', $user);

        $this->assertNull($item);
    }

    public function testSaveManyAfterFilteringCleanEntity(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $cleanItem = $Items->newEntity([
            'name' => 'Existing',
        ]);

        $this->assertTrue(
            $Items->save($cleanItem)
        );

        $newItem = $Items->newEntity([
            'name' => 'New',
        ]);

        $this->assertTrue(
            $Items->saveMany([$cleanItem, $newItem])
        );

        $this->assertSame(
            2,
            $Items->find()->count()
        );

        $this->assertTrue(
            $Items->exists(['name' => 'New'])
        );
    }

    public function testSaveManyErrors(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $items = $Items->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $items[1]->setError('name', 'Invalid value.');

        $this->assertFalse(
            $Items->saveMany($items)
        );
    }

    public function testSaveManyKeyedSingle(): void
    {
        $Items = $this->modelRegistry->use('Items');

        foreach ([1, 'item'] as $key) {
            $item = $Items->newEntity([
                'name' => 'Test '.$key,
            ]);

            $this->assertTrue(
                $Items->saveMany([$key => $item])
            );

            $this->assertTrue(
                $Items->exists(['name' => 'Test '.$key])
            );
        }
    }

    public function testUpdate(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item = $Items->newEntity([
            'name' => 'Test',
        ]);

        $this->assertTrue(
            $Items->save($item)
        );

        $Items->patchEntity($item, [
            'name' => 'Test 2',
        ]);

        $this->assertTrue(
            $Items->save($item)
        );

        $this->assertFalse(
            $item->isDirty()
        );

        $item = $Items->get(1);

        $this->assertInstanceOf(
            Item::class,
            $item
        );

        $this->assertArraysAreIdentical(
            [
                'id' => 1,
                'name' => 'Test 2',
            ],
            $item->toArray()
        );
    }

    public function testUpdateIncompleteCompositePrimaryKey(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessageIs('Primary key values for model `CompositeItems` must not be null or missing.');

        $CompositeItems = $this->modelRegistry->use('CompositeItems');
        $item = $CompositeItems->newEntity([
            'tenant_id' => 1,
            'name' => 'Updated',
        ], validate: false, new: false);

        $CompositeItems->save($item);
    }

    public function testUpdateMany(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $this->assertFalse(
            $items[0]->isDirty()
        );

        $this->assertFalse(
            $items[1]->isDirty()
        );

        $Items->patchEntities($items, [
            [
                'name' => 'Test 3',
            ],
            [
                'name' => 'Test 4',
            ],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $items = $Items->find()->toArray();

        $this->assertArraysAreIdentical(
            [
                [
                    'id' => 1,
                    'name' => 'Test 3',
                ],
                [
                    'id' => 2,
                    'name' => 'Test 4',
                ],
            ],
            array_map(
                static fn(Item $item): array => $item->toArray(),
                $items,
            )
        );
    }

    public function testUpdateManyBatch(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $data = [];

        for ($i = 0; $i < 1000; $i++) {
            $data[] = [
                'name' => 'Test',
            ];
        }

        $items = $Items->newEntities($data);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $data = [];

        foreach ($items as $i => $item) {
            $data[] = [
                'name' => 'Test '.($i + 1),
            ];
        }

        $Items->patchEntities($items, $data);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $items = $Items->find()->toArray();

        $this->assertArraysAreIdentical(
            array_map(
                static fn(int $i): string => 'Test '.$i,
                range(1, 1000)
            ),
            array_map(
                static fn(Item $item): string => $item->name,
                $items
            )
        );
    }
}
