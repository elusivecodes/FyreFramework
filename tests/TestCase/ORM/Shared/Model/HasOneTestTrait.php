<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use Fyre\ORM\Exceptions\OrmException;
use Fyre\ORM\Queries\SelectQuery;
use Tests\Mock\Entities\Address;
use Tests\Mock\Entities\User;

use function array_map;

trait HasOneTestTrait
{
    public function testHasOneDelete(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'address' => [
                'suburb' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $this->assertTrue(
            $Users->delete($user)
        );

        $this->assertSame(
            0,
            $Users->find()->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Addresses')->find()->count()
        );
    }

    public function testHasOneDeleteMany(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
                'address' => [
                    'suburb' => 'Test 1',
                ],
            ],
            [
                'name' => 'Test 2',
                'address' => [
                    'suburb' => 'Test 2',
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertTrue(
            $Users->deleteMany($users)
        );

        $this->assertSame(
            0,
            $Users->find()->count()
        );

        $this->assertSame(
            0,
            $this->modelRegistry->use('Addresses')->find()->count()
        );
    }

    public function testHasOneFind(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'address' => [
                'suburb' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $user = $Users->get(1, contain: [
            'Addresses',
        ]);

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $this->assertInstanceOf(
            Address::class,
            $user->address
        );

        $this->assertSame(
            1,
            $user->id
        );

        $this->assertSame(
            1,
            $user->address->id
        );

        $this->assertFalse(
            $user->isNew()
        );

        $this->assertFalse(
            $user->address->isNew()
        );
    }

    public function testHasOneFindCallback(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessageIs('Contain option `callback` cannot be used with the join strategy.');

        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'address' => [
                'suburb' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $user = $Users->get(1, contain: [
            'Addresses' => [
                'callback' => static fn(SelectQuery $query): SelectQuery => $query->where(['Addresses.id' => 1]),
            ],
        ]);
    }

    public function testHasOneFindRelated(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'address' => [
                'suburb' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $user = $Users->get(1);

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $address = $Users->Addresses->findRelated([$user])->first();

        $this->assertInstanceOf(
            Address::class,
            $address
        );

        $this->assertSame(
            1,
            $address->id
        );
    }

    public function testHasOneInsert(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'address' => [
                'suburb' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $this->assertSame(
            1,
            $user->id
        );

        $this->assertSame(
            1,
            $user->address->id
        );

        $this->assertSame(
            1,
            $user->address->user_id
        );

        $this->assertFalse(
            $user->isNew()
        );

        $this->assertFalse(
            $user->address->isNew()
        );

        $this->assertFalse(
            $user->isDirty()
        );

        $this->assertFalse(
            $user->address->isDirty()
        );
    }

    public function testHasOneInsertMany(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
                'address' => [
                    'suburb' => 'Test 1',
                ],
            ],
            [
                'name' => 'Test 2',
                'address' => [
                    'suburb' => 'Test 2',
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertArraysAreIdentical(
            [1, 2],
            array_map(
                static fn(User $user): int|null => $user->id,
                $users
            )
        );

        $this->assertArraysAreIdentical(
            [1, 2],
            array_map(
                static fn(User $user): int|null => $user->address->id,
                $users
            )
        );

        $this->assertArraysAreIdentical(
            [1, 2],
            array_map(
                static fn(User $user): int|null => $user->address->user_id,
                $users
            )
        );

        $this->assertFalse(
            $users[0]->isNew()
        );

        $this->assertFalse(
            $users[1]->isNew()
        );

        $this->assertFalse(
            $users[0]->address->isNew()
        );

        $this->assertFalse(
            $users[1]->address->isNew()
        );

        $this->assertFalse(
            $users[0]->isDirty()
        );

        $this->assertFalse(
            $users[1]->isDirty()
        );

        $this->assertFalse(
            $users[0]->address->isDirty()
        );

        $this->assertFalse(
            $users[1]->address->isDirty()
        );
    }

    public function testHasOneStrategyFind(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'address' => [
                'suburb' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $user = $Users->get(1, contain: [
            'Addresses' => [
                'strategy' => 'select',
            ],
        ]);

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $this->assertInstanceOf(
            Address::class,
            $user->address
        );

        $this->assertSame(
            1,
            $user->id
        );

        $this->assertSame(
            1,
            $user->address->id
        );

        $this->assertFalse(
            $user->isNew()
        );

        $this->assertFalse(
            $user->address->isNew()
        );
    }

    public function testHasOneStrategyFindCallback(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
            'address' => [
                'suburb' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $user = $Users->get(1, contain: [
            'Addresses' => [
                'strategy' => 'select',
                'callback' => static fn(SelectQuery $query): SelectQuery => $query->where(['Addresses.id' => 2]),
            ],
        ]);

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $this->assertSame(
            1,
            $user->id
        );

        $this->assertNull(
            $user->address
        );

        $this->assertFalse(
            $user->isNew()
        );
    }

    public function testHasOneUpdate(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test 1',
            'address' => [
                'suburb' => 'Test 1',
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $Users->patchEntity($user, [
            'name' => 'Test 2',
            'address' => [
                'id' => 1,
                'suburb' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $this->assertFalse(
            $user->isDirty()
        );

        $this->assertFalse(
            $user->address->isDirty()
        );

        $user = $Users->get(1, contain: [
            'Addresses',
        ]);

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $this->assertInstanceOf(
            Address::class,
            $user->address
        );

        $this->assertSame(
            'Test 2',
            $user->name
        );

        $this->assertSame(
            'Test 2',
            $user->address->suburb
        );
    }

    public function testHasOneUpdateMany(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
                'address' => [
                    'suburb' => 'Test 1',
                ],
            ],
            [
                'name' => 'Test 2',
                'address' => [
                    'suburb' => 'Test 2',
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $Users->patchEntities($users, [
            [
                'name' => 'Test 3',
                'address' => [
                    'id' => 1,
                    'suburb' => 'Test 3',
                ],
            ],
            [
                'name' => 'Test 4',
                'address' => [
                    'id' => 2,
                    'suburb' => 'Test 4',
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertFalse(
            $users[0]->isDirty()
        );

        $this->assertFalse(
            $users[1]->isDirty()
        );

        $this->assertFalse(
            $users[0]->address->isDirty()
        );

        $this->assertFalse(
            $users[1]->address->isDirty()
        );

        $users = $Users->find(contain: [
            'Addresses',
        ])->toArray();

        $this->assertSame(
            'Test 3',
            $users[0]->name
        );

        $this->assertSame(
            'Test 3',
            $users[0]->address->suburb
        );

        $this->assertSame(
            'Test 4',
            $users[1]->name
        );

        $this->assertSame(
            'Test 4',
            $users[1]->address->suburb
        );
    }
}
