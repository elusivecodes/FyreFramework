<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use Fyre\ORM\Exceptions\OrmException;
use Fyre\ORM\Queries\SelectQuery;
use Tests\Mock\Entities\Address;
use Tests\Mock\Entities\User;

use function array_map;

trait BelongsToTestTrait
{
    public function testBelongsToDelete(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => 'Test',
            'user' => [
                'name' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Addresses->save($address)
        );

        $this->assertTrue(
            $Addresses->delete($address)
        );

        $this->assertSame(
            0,
            $Addresses->find()->count()
        );

        $this->assertSame(
            1,
            $this->modelRegistry->use('Users')->find()->count()
        );
    }

    public function testBelongsToDeleteMany(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $addresses = $Addresses->newEntities([
            [
                'suburb' => 'Test 1',
                'user' => [
                    'name' => 'Test 1',
                ],
            ],
            [
                'suburb' => 'Test 2',
                'user' => [
                    'name' => 'Test 2',
                ],
            ],
        ]);

        $this->assertTrue(
            $Addresses->saveMany($addresses)
        );

        $this->assertTrue(
            $Addresses->deleteMany($addresses)
        );

        $this->assertSame(
            0,
            $Addresses->find()->count()
        );

        $this->assertSame(
            2,
            $this->modelRegistry->use('Users')->find()->count()
        );
    }

    public function testBelongsToFind(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => 'Test',
            'user' => [
                'name' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Addresses->save($address)
        );

        $address = $Addresses->get(1, contain: [
            'Users',
        ]);

        $this->assertInstanceOf(
            Address::class,
            $address
        );

        $this->assertInstanceOf(
            User::class,
            $address->user
        );

        $this->assertSame(
            1,
            $address->id
        );

        $this->assertSame(
            1,
            $address->user->id
        );

        $this->assertFalse(
            $address->isNew()
        );

        $this->assertFalse(
            $address->user->isNew()
        );
    }

    public function testBelongsToFindCallback(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessage('Contain option `callback` cannot be used with the join strategy.');

        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => 'Test',
            'user' => [
                'name' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Addresses->save($address)
        );

        $address = $Addresses->get(1, contain: [
            'Users' => [
                'callback' => static fn(SelectQuery $query): SelectQuery => $query->where(['Users.id' => 1]),
            ],
        ]);
    }

    public function testBelongsToFindRelated(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => 'Test',
            'user' => [
                'name' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Addresses->save($address)
        );

        $address = $Addresses->get(1);

        $this->assertInstanceOf(
            Address::class,
            $address
        );

        $user = $Addresses->Users->findRelated([$address])->first();

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $this->assertSame(
            1,
            $user->id
        );
    }

    public function testBelongsToInsert(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => 'Test',
            'user' => [
                'name' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Addresses->save($address)
        );

        $this->assertSame(
            1,
            $address->id
        );

        $this->assertSame(
            1,
            $address->user->id
        );

        $this->assertSame(
            1,
            $address->user_id
        );

        $this->assertFalse(
            $address->isNew()
        );

        $this->assertFalse(
            $address->user->isNew()
        );

        $this->assertFalse(
            $address->isDirty()
        );

        $this->assertFalse(
            $address->user->isDirty()
        );
    }

    public function testBelongsToInsertMany(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $addresses = [
            $Addresses->newEntity([
                'suburb' => 'Test 1',
                'user' => [
                    'name' => 'Test 1',
                ],
            ]),
            $Addresses->newEntity([
                'suburb' => 'Test 2',
                'user' => [
                    'name' => 'Test 2',
                ],
            ]),
        ];

        $this->assertTrue(
            $Addresses->saveMany($addresses)
        );

        $this->assertArraysAreIdentical(
            [1, 2],
            array_map(
                static fn(Address $address): int|null => $address->id,
                $addresses
            )
        );

        $this->assertArraysAreIdentical(
            [1, 2],
            array_map(
                static fn(Address $address): int|null => $address->user->id,
                $addresses
            )
        );

        $this->assertArraysAreIdentical(
            [1, 2],
            array_map(
                static fn(Address $address): int|null => $address->user_id,
                $addresses
            )
        );

        $this->assertFalse(
            $addresses[0]->isNew()
        );

        $this->assertFalse(
            $addresses[1]->isNew()
        );

        $this->assertFalse(
            $addresses[0]->user->isNew()
        );

        $this->assertFalse(
            $addresses[1]->user->isNew()
        );

        $this->assertFalse(
            $addresses[0]->isDirty()
        );

        $this->assertFalse(
            $addresses[1]->isDirty()
        );

        $this->assertFalse(
            $addresses[0]->user->isDirty()
        );

        $this->assertFalse(
            $addresses[1]->user->isDirty()
        );
    }

    public function testBelongsToStrategyFind(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => 'Test',
            'user' => [
                'name' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Addresses->save($address)
        );

        $address = $Addresses->get(1, contain: [
            'Users' => [
                'strategy' => 'select',
            ],
        ]);

        $this->assertInstanceOf(
            Address::class,
            $address
        );

        $this->assertInstanceOf(
            User::class,
            $address->user
        );

        $this->assertSame(
            1,
            $address->id
        );

        $this->assertSame(
            1,
            $address->user->id
        );

        $this->assertFalse(
            $address->isNew()
        );

        $this->assertFalse(
            $address->user->isNew()
        );
    }

    public function testBelongsToStrategyFindCallback(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => 'Test',
            'user' => [
                'name' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Addresses->save($address)
        );

        $address = $Addresses->get(1, contain: [
            'Users' => [
                'strategy' => 'select',
                'callback' => static fn(SelectQuery $query): SelectQuery => $query->where(['Users.id' => 2]),
            ],
        ]);

        $this->assertInstanceOf(
            Address::class,
            $address
        );

        $this->assertSame(
            1,
            $address->id
        );

        $this->assertNull(
            $address->user
        );

        $this->assertFalse(
            $address->isNew()
        );
    }

    public function testBelongsToUpdate(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $address = $Addresses->newEntity([
            'suburb' => 'Test',
            'user' => [
                'name' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Addresses->save($address)
        );

        $Addresses->patchEntity($address, [
            'suburb' => 'Test 2',
            'user' => [
                'id' => 1,
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Addresses->save($address)
        );

        $this->assertFalse(
            $address->isDirty()
        );

        $this->assertFalse(
            $address->user->isDirty()
        );

        $address = $Addresses->get(1, contain: [
            'Users',
        ]);

        $this->assertInstanceOf(
            Address::class,
            $address
        );

        $this->assertInstanceOf(
            User::class,
            $address->user
        );

        $this->assertSame(
            'Test 2',
            $address->suburb
        );

        $this->assertSame(
            'Test 2',
            $address->user->name
        );
    }

    public function testBelongsToUpdateMany(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $addresses = $Addresses->newEntities([
            [
                'suburb' => 'Test 1',
                'user' => [
                    'name' => 'Test 1',
                ],
            ],
            [
                'suburb' => 'Test 2',
                'user' => [
                    'name' => 'Test 2',
                ],
            ],
        ]);

        $this->assertTrue(
            $Addresses->saveMany($addresses)
        );

        $Addresses->patchEntities($addresses, [
            [
                'suburb' => 'Test 3',
                'user' => [
                    'name' => 'Test 3',
                ],
            ],
            [
                'suburb' => 'Test 4',
                'user' => [
                    'name' => 'Test 4',
                ],
            ],
        ]);

        $this->assertTrue(
            $Addresses->saveMany($addresses)
        );

        $this->assertFalse(
            $addresses[0]->isDirty()
        );

        $this->assertFalse(
            $addresses[1]->isDirty()
        );

        $this->assertFalse(
            $addresses[0]->user->isDirty()
        );

        $this->assertFalse(
            $addresses[1]->user->isDirty()
        );

        $addresses = $Addresses->find(contain: [
            'Users',
        ])->toArray();

        $this->assertSame(
            'Test 3',
            $addresses[0]->suburb
        );

        $this->assertSame(
            'Test 3',
            $addresses[0]->user->name
        );

        $this->assertSame(
            'Test 4',
            $addresses[1]->suburb
        );

        $this->assertSame(
            'Test 4',
            $addresses[1]->user->name
        );
    }
}
