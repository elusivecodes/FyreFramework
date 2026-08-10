<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\MariaDb\Traits;

use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Query;
use Fyre\ORM\Entity;
use Fyre\Utility\DateTime\DateTime;
use Generator;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Entities\Address;
use Tests\TestCase\ORM\MariaDb\MariaDbConnectionTrait;

use function substr_count;

final class SoftDeleteTest extends TestCase
{
    use MariaDbConnectionTrait;

    public function testDelete(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $Addresses = $this->modelRegistry->use('Addresses');
        $Posts = $this->modelRegistry->use('Posts');
        $Comments = $this->modelRegistry->use('Comments');

        $Users->Addresses->setDependent(true);
        $Posts->Comments->setDependent(true);

        $user = $Users->newEntity([
            'name' => 'Test',
            'posts' => [
                [
                    'title' => 'Test 1',
                    'content' => 'This is the content.',
                    'comments' => [
                        [
                            'content' => 'This is a comment',
                            'user' => [
                                'name' => 'Test 2',
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Test 2',
                    'content' => 'This is the content.',
                    'comments' => [
                        [
                            'content' => 'This is a comment',
                            'user' => [
                                'name' => 'Test 3',
                            ],
                        ],
                    ],
                ],
            ],
            'address' => [
                'suburb' => 'Test',
            ],
        ], associated: [
            'Posts.Comments.Users',
            'Addresses',
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $this->assertTrue(
            $Users->delete($user)
        );

        $this->assertInstanceOf(
            DateTime::class,
            $user->deleted
        );

        $this->assertSame(
            2,
            $Users->find()->count()
        );

        $this->assertSame(
            3,
            $Users->find(deleted: true)->count()
        );

        $this->assertSame(
            0,
            $Posts->find()->count()
        );

        $this->assertSame(
            2,
            $Posts->find(deleted: true)->count()
        );

        $this->assertSame(
            0,
            $Addresses->find()->count()
        );

        $this->assertSame(
            1,
            $Addresses->find(deleted: true)->count()
        );

        $this->assertSame(
            0,
            $Comments->find()->count()
        );

        $this->assertSame(
            2,
            $Comments->find(deleted: true)->count()
        );
    }

    public function testFindOnlyDeleted(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertTrue(
            $Users->delete($users[0])
        );

        $this->assertSame(
            [1],
            $Users->findOnlyDeleted(
                conditions: static fn(Query $query): ConditionExpression => $query->expr()
                    ->eq('Users.name', 'Test 1')
            )
                ->all()
                ->map(static fn(Entity $item): int|null => $item->id)
                ->toArray()
        );
    }

    public function testFindWithDeleted(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertTrue(
            $Users->delete($users[0])
        );

        $this->assertSame(
            1,
            $Users->find()->count()
        );

        $this->assertSame(
            [1, 2],
            $Users->findWithDeleted(
                conditions: static fn(Query $query): ConditionExpression => $query->expr()
                    ->gt('Users.id', 0)
            )
                ->orderBy(['id' => 'ASC'])
                ->all()
                ->map(static fn(Entity $item): int|null => $item->id)
                ->toArray()
        );
    }

    public function testInnerJoinWithFiltersSoftDeletedRelations(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $Posts = $this->modelRegistry->use('Posts');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
                'posts' => [
                    [
                        'title' => 'Test 1',
                        'content' => 'This is the content.',
                    ],
                ],
            ],
            [
                'name' => 'Test 2',
                'posts' => [
                    [
                        'title' => 'Test 2',
                        'content' => 'This is the content.',
                    ],
                ],
            ],
        ], associated: ['Posts']);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertTrue(
            $Posts->delete($users[0]->posts[0])
        );

        $this->assertSame(
            [2],
            $Users->find()
                ->innerJoinWith('Posts')
                ->orderBy(['Users.id' => 'ASC'])
                ->all()
                ->map(static fn(Entity $user): int|null => $user->id)
                ->toArray()
        );

        $this->assertSame(
            [1, 2],
            $Users->findWithDeleted()
                ->innerJoinWith('Posts')
                ->orderBy(['Users.id' => 'ASC'])
                ->all()
                ->map(static fn(Entity $user): int|null => $user->id)
                ->toArray()
        );
    }

    public function testJoinContainFiltersSoftDeletedRelations(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $Addresses = $this->modelRegistry->use('Addresses');

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
        ], associated: ['Addresses']);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertTrue(
            $Addresses->delete($users[0]->address)
        );

        $this->assertSame(
            [null, 2],
            $Users->find()
                ->contain('Addresses')
                ->orderBy(['Users.id' => 'ASC'])
                ->all()
                ->map(static function(Entity $user): int|null {
                    $address = $user->get('address');

                    return $address instanceof Address ? $address->id : null;
                })
                ->toArray()
        );

        $this->assertSame(
            [1, 2],
            $Users->findWithDeleted()
                ->contain('Addresses')
                ->orderBy(['Users.id' => 'ASC'])
                ->all()
                ->map(static function(Entity $user): int|null {
                    $address = $user->get('address');

                    return $address instanceof Address ? $address->id : null;
                })
                ->toArray()
        );
    }

    public function testJoinContainPathBuildJoinTriggeredOnce(): void
    {
        $sql = $this->modelRegistry->use('Users')
            ->find()
            ->contain([
                'Addresses' => [
                    'autoFields' => false,
                ],
            ])
            ->innerJoinWith('Addresses')
            ->disableAutoFields()
            ->sql();

        $this->assertSame(
            1,
            substr_count($sql, '`Addresses`.`deleted` IS NULL')
        );
    }

    public function testPurge(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertTrue(
            $Users->purge($users[0])
        );

        $this->assertSame(
            [2],
            $Users->find(deleted: true)
                ->all()
                ->map(static fn(Entity $item): int|null => $item->id)
                ->toArray()
        );
    }

    public function testPurgeMany(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertTrue(
            $Users->purgeMany($users)
        );

        $this->assertSame(
            0,
            $Users->find(deleted: true)->count()
        );
    }

    public function testRestore(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertTrue(
            $Users->delete($users[0])
        );

        $this->assertSame(
            1,
            $Users->find()->count()
        );

        $this->assertSame(
            2,
            $Users->find(deleted: true)->count()
        );

        $this->assertTrue(
            $Users->restore($users[0])
        );

        $this->assertNull(
            $users[0]->deleted
        );

        $this->assertSame(
            2,
            $Users->find()->count()
        );

        $this->assertSame(
            [1, 2],
            $Users->find()
                ->orderBy(['id' => 'ASC'])
                ->all()
                ->map(static fn(Entity $item): int|null => $item->id)
                ->toArray()
        );
    }

    public function testRestoreMany(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
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
            2,
            $Users->find(deleted: true)->count()
        );

        $this->assertTrue(
            $Users->restoreMany($users)
        );

        $this->assertSame(
            2,
            $Users->find()->count()
        );

        $this->assertSame(
            [1, 2],
            $Users->find()
                ->orderBy(['id' => 'ASC'])
                ->all()
                ->map(static fn(Entity $item): int|null => $item->id)
                ->toArray()
        );
    }

    public function testRestoreManyGenerator(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $this->assertTrue(
            $Users->deleteMany($users)
        );

        $generator = static function() use ($users): Generator {
            yield from $users;
        };

        $this->assertTrue(
            $Users->restoreMany($generator())
        );

        $this->assertSame(
            2,
            $Users->find()->count()
        );
    }

    #[Before(-1)]
    protected function changeNamespace(): void
    {
        $this->modelRegistry->clearNamespaces();
        $this->modelRegistry->addNamespace('Tests\Mock\Models\ORM\SoftDelete');
    }
}
