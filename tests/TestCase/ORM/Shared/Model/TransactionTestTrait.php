<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use Error;
use Fyre\DB\ConnectionManager;
use Fyre\DB\Exceptions\DbException;
use Fyre\Event\Event;
use Fyre\Event\EventManager;
use Fyre\Log\LogManager;
use Fyre\ORM\Entity;
use Fyre\ORM\Exceptions\OrmException;
use Fyre\ORM\Model;
use RuntimeException;
use Tests\Mock\Entities\Item;
use Tests\Mock\Entities\MockEntity;
use Tests\Mock\Entities\Post;
use Throwable;

trait TransactionTestTrait
{
    public function testDeleteExceptionRollsBack(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item = $Items->newEntity([
            'name' => 'Test',
        ]);

        $this->assertTrue(
            $Items->save($item)
        );

        $commits = [];
        $exception = new RuntimeException('Operation failed.');

        $Items->getEventManager()->on('ORM.afterDelete', function(Event $event, Item $entity) use ($Items, $exception, &$commits): void {
            $this->db->afterCommit(static function() use (&$commits): void {
                $commits[] = 'failed';
            });

            $Items->setTemporaryField($entity, 'temporary', 'value');

            throw $exception;
        });

        $caught = null;

        try {
            $Items->delete($item);
        } catch (Throwable $e) {
            $caught = $e;
        }

        $this->assertSame(
            $exception,
            $caught
        );

        $this->assertSame(
            0,
            $this->db->getSavePointLevel()
        );

        $this->assertFalse(
            $this->db->inTransaction()
        );

        $this->assertSame(
            1,
            $Items->find()->count()
        );

        $this->assertSame(
            [],
            $commits
        );

        $this->assertTrue(
            $Items->exists(['id' => $item->id])
        );

        $this->assertFalse(
            $item->isNew()
        );

        $this->assertFalse(
            $item->hasValue('temporary')
        );
    }

    public function testDeleteExceptionRollsBackNestedTransaction(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item = $Items->newEntity([
            'name' => 'Test',
        ]);

        $this->assertTrue(
            $Items->save($item)
        );

        $this->db->begin();

        $existing = $Items->newEntity([
            'name' => 'Existing',
        ]);

        $this->assertTrue(
            $Items->save($existing)
        );

        $commits = [];
        $this->db->afterCommit(static function() use (&$commits): void {
            $commits[] = 'outer';
        });

        $exception = new Error('Operation failed.');

        $Items->getEventManager()->on('ORM.afterDelete', function(Event $event, Item $entity) use ($Items, $exception, &$commits): void {
            $this->db->afterCommit(static function() use (&$commits): void {
                $commits[] = 'failed';
            });

            $Items->setTemporaryField($entity, 'temporary', 'value');

            throw $exception;
        });

        $caught = null;

        try {
            $Items->delete($item);
        } catch (Throwable $e) {
            $caught = $e;
        }

        $this->assertSame(
            $exception,
            $caught
        );

        $this->assertSame(
            1,
            $this->db->getSavePointLevel()
        );

        $this->assertTrue(
            $this->db->inTransaction()
        );

        $this->assertSame(
            2,
            $Items->find()->count()
        );

        $this->assertSame(
            [],
            $commits
        );

        $this->assertTrue(
            $Items->exists(['id' => $item->id])
        );

        $this->assertFalse(
            $item->isNew()
        );

        $this->assertFalse(
            $item->hasValue('temporary')
        );

        $this->db->commit();

        $this->assertSame(
            ['outer'],
            $commits
        );

        $this->assertFalse(
            $this->db->inTransaction()
        );

        $this->assertTrue(
            $Items->exists(['name' => 'Existing'])
        );
    }

    public function testDeleteManyExceptionRollsBack(): void
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

        $commits = [];
        $exception = new RuntimeException('Operation failed.');

        $Items->getEventManager()->on('ORM.afterDelete', function(Event $event, Item $entity) use ($Items, $exception, &$commits): void {
            $this->db->afterCommit(static function() use (&$commits): void {
                $commits[] = 'failed';
            });

            if ($entity->get('name') !== 'Test 2') {
                return;
            }

            $Items->setTemporaryField($entity, 'temporary', 'value');

            throw $exception;
        });

        $caught = null;

        try {
            $Items->deleteMany($items);
        } catch (Throwable $e) {
            $caught = $e;
        }

        $this->assertSame(
            $exception,
            $caught
        );

        $this->assertSame(
            0,
            $this->db->getSavePointLevel()
        );

        $this->assertFalse(
            $this->db->inTransaction()
        );

        $this->assertSame(
            2,
            $Items->find()->count()
        );

        $this->assertSame(
            [],
            $commits
        );

        foreach ($items as $item) {
            $this->assertTrue(
                $Items->exists(['id' => $item->id])
            );

            $this->assertFalse(
                $item->isNew()
            );

            $this->assertFalse(
                $item->hasValue('temporary')
            );
        }
    }

    public function testDeleteManyExceptionRollsBackNestedTransaction(): void
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

        $this->db->begin();

        $existing = $Items->newEntity([
            'name' => 'Existing',
        ]);

        $this->assertTrue(
            $Items->save($existing)
        );

        $commits = [];
        $this->db->afterCommit(static function() use (&$commits): void {
            $commits[] = 'outer';
        });

        $exception = new Error('Operation failed.');

        $Items->getEventManager()->on('ORM.afterDelete', function(Event $event, Item $entity) use ($Items, $exception, &$commits): void {
            $this->db->afterCommit(static function() use (&$commits): void {
                $commits[] = 'failed';
            });

            if ($entity->get('name') !== 'Test 2') {
                return;
            }

            $Items->setTemporaryField($entity, 'temporary', 'value');

            throw $exception;
        });

        $caught = null;

        try {
            $Items->deleteMany($items);
        } catch (Throwable $e) {
            $caught = $e;
        }

        $this->assertSame(
            $exception,
            $caught
        );

        $this->assertSame(
            1,
            $this->db->getSavePointLevel()
        );

        $this->assertTrue(
            $this->db->inTransaction()
        );

        $this->assertSame(
            3,
            $Items->find()->count()
        );

        $this->assertSame(
            [],
            $commits
        );

        foreach ($items as $item) {
            $this->assertTrue(
                $Items->exists(['id' => $item->id])
            );

            $this->assertFalse(
                $item->isNew()
            );

            $this->assertFalse(
                $item->hasValue('temporary')
            );
        }

        $this->db->commit();

        $this->assertSame(
            ['outer'],
            $commits
        );

        $this->assertFalse(
            $this->db->inTransaction()
        );

        $this->assertTrue(
            $Items->exists(['name' => 'Existing'])
        );
    }

    public function testDeleteManyPreservesUnsavedChanges(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $items = $Items->newEntities([
            ['name' => 'First'],
            ['name' => 'Second'],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        foreach ($items as $item) {
            $item->name = 'Unsaved';
        }

        $this->assertTrue(
            $Items->deleteMany($items)
        );
        $this->assertSame(
            0,
            $Items->find()->count()
        );

        foreach ($items as $i => $item) {
            $this->assertTrue(
                $item->isDirty('name')
            );
            $this->assertSame(
                ['First', 'Second'][$i],
                $item->getOriginal('name')
            );
        }
    }

    public function testDeletePreservesUnsavedChanges(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $item = $Items->newEntity([
            'name' => 'Original',
        ]);

        $this->assertTrue(
            $Items->save($item)
        );

        $item->name = 'Unsaved';

        $this->assertTrue(
            $Items->delete($item)
        );
        $this->assertSame(
            0,
            $Items->find()->count()
        );
        $this->assertTrue(
            $item->isDirty('name')
        );
        $this->assertSame(
            'Original',
            $item->getOriginal('name')
        );
    }

    public function testDeleteUnlinkOuterRollback(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $Posts = $this->modelRegistry->use('Posts');
        $Users->Posts->setDependent(false);
        $user = $Users->newEntity([
            'name' => 'Test',
            'posts' => [
                [
                    'title' => 'Post',
                ],
            ],
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $saved = [];
        $Posts->getEventManager()->on('ORM.afterSave', static function(Event $event, Post $entity) use (&$saved): void {
            $saved[] = $entity;
        });
        $this->db->begin();

        $this->assertTrue(
            $Users->delete($user)
        );
        $this->assertCount(
            1,
            $saved
        );
        $this->assertNull(
            $saved[0]->user_id
        );

        $this->db->rollback();

        $this->assertNotNull(
            $user->id
        );
        $this->assertNotNull(
            $saved[0]->id
        );
        $this->assertSame(
            $user->id,
            $saved[0]->user_id
        );
        $this->assertSame(
            $user->id,
            $Posts->get($saved[0]->id)?->user_id
        );
        $this->assertTrue(
            $Users->exists(['id' => $user->id])
        );
    }

    public function testSaveAfterSaveChangesRemainDirty(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $item = $Items->newEntity([
            'name' => 'Saved',
        ]);
        $item->setHidden(['name']);

        $Items->getEventManager()->on('ORM.afterSave', static function(Event $event, Item $entity): void {
            $entity->name = 'Later';
        });

        $this->assertTrue(
            $Items->save($item)
        );
        $this->assertNotNull(
            $item->id
        );
        $this->assertSame(
            'Saved',
            $Items->get($item->id)?->name
        );
        $this->assertSame(
            'Later',
            $item->name
        );
        $this->assertSame(
            'Saved',
            $item->getOriginal('name')
        );
        $this->assertTrue(
            $item->isDirty('name')
        );
        $this->assertFalse(
            $item->isNew()
        );
    }

    public function testSaveAfterSaveCommitChangesRemainDirty(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $item = $Items->newEntity([
            'name' => 'Saved',
        ]);

        $Items->getEventManager()->on('ORM.afterSaveCommit', static function(Event $event, Item $entity): void {
            $entity->name = 'Later';
        });

        $this->assertTrue(
            $Items->save($item)
        );
        $this->assertNotNull(
            $item->id
        );
        $this->assertSame(
            'Saved',
            $Items->get($item->id)?->name
        );
        $this->assertSame(
            'Later',
            $item->name
        );
        $this->assertSame(
            'Saved',
            $item->getOriginal('name')
        );
        $this->assertTrue(
            $item->isDirty('name')
        );
    }

    public function testSaveChecksExistenceOnWriteConnection(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $reader = $this->container->use(ConnectionManager::class)->build($this->db->getConfig());
        $Items->setConnection($reader, Model::READ);

        $item = $Items->newEntity([
            'name' => 'Original',
        ]);

        try {
            $this->db->begin();

            $this->assertTrue(
                $Items->save($item)
            );

            $item->name = 'Updated';

            $this->assertTrue(
                $Items->save($item)
            );

            $this->db->commit();

            $this->assertSame(
                1,
                $Items->find()->count()
            );
            $this->assertNotNull(
                $item->id
            );
            $this->assertSame(
                'Updated',
                $Items->get($item->id)?->name
            );
        } finally {
            while ($this->db->getSavePointLevel() > 0) {
                $this->db->rollback();
            }

            $reader->disconnect();
        }
    }

    public function testSaveCommitExceptionRollsBack(): void
    {
        $connection = $this->getStubBuilder($this->db::class)
            ->setConstructorArgs([
                $this->container,
                $this->container->use(EventManager::class),
                $this->container->use(LogManager::class),
                $this->db->getConfig(),
            ])
            ->onlyMethods(['commit'])
            ->getStub();

        $exception = new DbException('Commit failed.');

        $connection->method('commit')
            ->willThrowException($exception);

        try {
            $Items = $this->modelRegistry->use('Items');
            $Items->setConnection($connection);

            $item = $Items->newEntity([
                'name' => 'Test',
            ]);

            $caught = null;

            try {
                $Items->save($item);
            } catch (DbException $e) {
                $caught = $e;
            }

            $this->assertSame(
                $exception,
                $caught
            );

            $this->assertSame(
                0,
                $connection->getSavePointLevel()
            );

            $this->assertFalse(
                $connection->inTransaction()
            );

            $this->assertSame(
                0,
                $Items->find()->count()
            );

            $this->assertNull(
                $item->get('id')
            );

            $this->assertTrue(
                $item->isNew()
            );

        } finally {
            while ($connection->getSavePointLevel() > 0) {
                $connection->rollback();
            }

            $connection->disconnect();
        }
    }

    public function testSaveExceptionRollsBack(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item = $Items->newEntity([
            'name' => 'Test',
        ]);

        $commits = [];
        $exception = new RuntimeException('Operation failed.');

        $Items->getEventManager()->on('ORM.afterSave', function(Event $event, Item $entity) use ($Items, $exception, &$commits): void {
            $this->db->afterCommit(static function() use (&$commits): void {
                $commits[] = 'failed';
            });

            $Items->setTemporaryField($entity, 'temporary', 'value');

            throw $exception;
        });

        $caught = null;

        try {
            $Items->save($item);
        } catch (Throwable $e) {
            $caught = $e;
        }

        $this->assertSame(
            $exception,
            $caught
        );

        $this->assertSame(
            0,
            $this->db->getSavePointLevel()
        );

        $this->assertFalse(
            $this->db->inTransaction()
        );

        $this->assertSame(
            0,
            $Items->find()->count()
        );

        $this->assertSame(
            [],
            $commits
        );

        $this->assertNull(
            $item->id
        );

        $this->assertTrue(
            $item->isNew()
        );

        $this->assertFalse(
            $item->hasValue('temporary')
        );
    }

    public function testSaveExceptionRollsBackNestedTransaction(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item = $Items->newEntity([
            'name' => 'Test',
        ]);

        $this->db->begin();

        $existing = $Items->newEntity([
            'name' => 'Existing',
        ]);

        $this->assertTrue(
            $Items->save($existing)
        );

        $commits = [];
        $this->db->afterCommit(static function() use (&$commits): void {
            $commits[] = 'outer';
        });

        $exception = new Error('Operation failed.');

        $Items->getEventManager()->on('ORM.afterSave', function(Event $event, Item $entity) use ($Items, $exception, &$commits): void {
            $this->db->afterCommit(static function() use (&$commits): void {
                $commits[] = 'failed';
            });

            $Items->setTemporaryField($entity, 'temporary', 'value');

            throw $exception;
        });

        $caught = null;

        try {
            $Items->save($item);
        } catch (Throwable $e) {
            $caught = $e;
        }

        $this->assertSame(
            $exception,
            $caught
        );

        $this->assertSame(
            1,
            $this->db->getSavePointLevel()
        );

        $this->assertTrue(
            $this->db->inTransaction()
        );

        $this->assertSame(
            1,
            $Items->find()->count()
        );

        $this->assertSame(
            [],
            $commits
        );

        $this->assertNull(
            $item->id
        );

        $this->assertTrue(
            $item->isNew()
        );

        $this->assertFalse(
            $item->hasValue('temporary')
        );

        $this->db->commit();

        $this->assertSame(
            ['outer'],
            $commits
        );

        $this->assertFalse(
            $this->db->inTransaction()
        );

        $this->assertTrue(
            $Items->exists(['name' => 'Existing'])
        );
    }

    public function testSaveExistingChildrenOuterRollback(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $original = $Users->newEntity([
            'name' => 'Original',
            'posts' => [
                [
                    'title' => 'Post',
                ],
            ],
            'address' => [
                'suburb' => 'Test',
            ],
        ]);

        $this->assertTrue(
            $Users->save($original)
        );

        $post = $original->posts[0];
        $address = $original->address;
        $postId = $post->id;
        $addressId = $address->id;

        $this->assertNotNull(
            $postId
        );
        $this->assertNotNull(
            $addressId
        );

        $user = $Users->newEntity([
            'name' => 'New',
        ]);
        $user->set('posts', [$post]);
        $user->set('address', $address);
        $this->db->begin();

        $this->assertTrue(
            $Users->save($user)
        );
        $this->assertSame(
            $user->id,
            $post->user_id
        );
        $this->assertSame(
            $user->id,
            $address->user_id
        );

        $this->db->rollback();

        $this->assertFalse(
            $user->has('id')
        );
        $this->assertSame(
            $postId,
            $post->id
        );
        $this->assertSame(
            $addressId,
            $address->id
        );
        $this->assertSame(
            $original->id,
            $post->user_id
        );
        $this->assertSame(
            $original->id,
            $address->user_id
        );
        $this->assertFalse(
            $post->isNew()
        );
        $this->assertFalse(
            $address->isNew()
        );

        $this->assertTrue(
            $Users->save($user)
        );
        $this->assertNotNull(
            $user->id
        );
        $this->assertSame(
            $user->id,
            $this->modelRegistry->use('Posts')->get($postId)?->user_id
        );
        $this->assertSame(
            $user->id,
            $this->modelRegistry->use('Addresses')->get($addressId)?->user_id
        );
    }

    public function testSaveInnerRollbackKeepsChangesDirty(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $item = $Items->newEntity([
            'name' => 'Original',
        ]);

        $this->assertTrue(
            $Items->save($item)
        );

        $this->db->begin();

        foreach (['First', 'Outer'] as $name) {
            $item->name = $name;

            $this->assertTrue(
                $Items->save($item)
            );
        }

        $this->db->begin();
        $item->name = 'Inner';

        $this->assertTrue(
            $Items->save($item)
        );

        $this->db->rollback();
        $this->db->commit();

        $this->assertNotNull(
            $item->id
        );
        $this->assertSame(
            'Outer',
            $Items->get($item->id)?->name
        );
        $this->assertSame(
            'Inner',
            $item->name
        );
        $this->assertSame(
            'Outer',
            $item->getOriginal('name')
        );
        $this->assertTrue(
            $item->isDirty('name')
        );
        $this->assertTrue(
            $Items->save($item)
        );
        $this->assertSame(
            'Inner',
            $Items->get($item->id)->name
        );
        $this->assertFalse(
            $item->isDirty()
        );
    }

    public function testSaveInnerRollbackPreservesOuterInsert(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $item = $Items->newEntity([
            'name' => 'Test',
        ]);

        $this->db->begin();

        $this->assertTrue(
            $Items->save($item)
        );

        $id = $item->id;
        $this->db->begin();
        $item->set('name', 'Updated');

        $this->assertTrue(
            $Items->save($item)
        );

        $this->db->rollback();

        $this->assertNotNull(
            $id
        );
        $this->assertSame(
            $id,
            $item->id
        );
        $this->assertSame(
            'Test',
            $Items->get($id)?->name
        );

        $this->assertTrue(
            $Items->save($item)
        );

        $this->db->commit();

        $this->assertSame(
            'Updated',
            $Items->get($id)->name
        );
        $this->assertFalse(
            $item->isNew()
        );
    }

    public function testSaveJunctionOuterRollback(): void
    {
        $Posts = $this->modelRegistry->use('Posts');
        $Tags = $this->modelRegistry->use('Tags');
        $PostsTags = $this->modelRegistry->use('PostsTags');
        $tag = $Tags->newEntity([
            'tag' => 'Test',
        ]);

        $this->assertTrue(
            $Tags->save($tag)
        );

        $tagId = $tag->id;
        $join = $PostsTags->newEntity([
            'value' => 1,
        ]);
        $tag->set('_joinData', $join);
        $post = $Posts->newEntity([
            'title' => 'Post',
        ]);
        $post->set('tags', [$tag]);
        $this->db->begin();

        $this->assertTrue(
            $Posts->save($post)
        );

        $this->db->rollback();

        $this->assertSame(
            $tagId,
            $tag->id
        );
        $this->assertSame(
            $join,
            $tag->get('_joinData')
        );
        $this->assertFalse(
            $join->has('id')
        );
        $this->assertFalse(
            $join->has('post_id')
        );
        $this->assertFalse(
            $join->has('tag_id')
        );
        $this->assertTrue(
            $join->isNew()
        );
        $this->assertSame(
            1,
            $join->get('value')
        );
        $this->assertTrue(
            $Posts->save($post)
        );
        $this->assertNotNull(
            $post->id
        );
        $this->assertNotNull(
            $join->id
        );
        $this->assertSame(
            $post->id,
            $PostsTags->get($join->id)?->post_id
        );
        $this->assertSame(
            1,
            $Tags->find()->count()
        );
    }

    public function testSaveManyAfterSaveChangesRemainDirty(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $items = $Items->newEntities([
            ['name' => 'First'],
            ['name' => 'Second'],
        ]);

        $Items->getEventManager()->on('ORM.afterSave', static function(Event $event, Entity $entity) use ($items): void {
            if ($entity === $items[1]) {
                $items[0]->name = 'Later';
            }
        });

        $this->assertTrue(
            $Items->saveMany($items)
        );
        $this->assertNotNull(
            $items[0]->id
        );
        $this->assertSame(
            'First',
            $Items->get($items[0]->id)?->name
        );
        $this->assertSame(
            'Later',
            $items[0]->name
        );
        $this->assertSame(
            'First',
            $items[0]->getOriginal('name')
        );
        $this->assertTrue(
            $items[0]->isDirty('name')
        );
        $this->assertFalse(
            $items[1]->isDirty()
        );
    }

    public function testSaveManyChecksExistenceOnWriteConnection(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $reader = $this->container->use(ConnectionManager::class)->build($this->db->getConfig());
        $Items->setConnection($reader, Model::READ);

        $items = $Items->newEntities([
            ['name' => 'First'],
            ['name' => 'Second'],
        ]);

        try {
            $this->db->begin();

            $this->assertTrue(
                $Items->saveMany($items)
            );

            foreach ($items as $item) {
                $item->name = 'Updated';
            }

            $this->assertTrue(
                $Items->saveMany($items)
            );

            $this->db->commit();

            $this->assertSame(
                ['Updated', 'Updated'],
                $Items->find(orderBy: ['id' => 'ASC'])
                    ->all()
                    ->map(static fn(Entity $item): string => $item->name)
                    ->toArray()
            );
        } finally {
            while ($this->db->getSavePointLevel() > 0) {
                $this->db->rollback();
            }

            $reader->disconnect();
        }
    }

    public function testSaveManyDatabaseExceptionRollsBack(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'id' => 10,
                'name' => 'First',
            ],
            [
                'id' => 10,
                'name' => 'Duplicate',
            ],
        ]);

        $caught = null;

        try {
            $Items->saveMany($items, checkExists: false);
        } catch (DbException $e) {
            $caught = $e;
        }

        $this->assertInstanceOf(
            DbException::class,
            $caught
        );

        $this->assertSame(
            0,
            $this->db->getSavePointLevel()
        );

        $this->assertFalse(
            $this->db->inTransaction()
        );

        $this->assertSame(
            0,
            $Items->find()->count()
        );

        $later = $Items->newEntity([
            'name' => 'Later',
        ]);

        $this->assertTrue(
            $Items->save($later)
        );

        $this->assertFalse(
            $this->db->inTransaction()
        );

        $this->assertFalse(
            $later->isNew()
        );

        $this->assertTrue(
            $Items->exists(['name' => 'Later'])
        );
    }

    public function testSaveManyExceptionRollsBack(): void
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

        $commits = [];
        $exception = new RuntimeException('Operation failed.');

        $Items->getEventManager()->on('ORM.afterSave', function(Event $event, Item $entity) use ($Items, $exception, &$commits): void {
            $this->db->afterCommit(static function() use (&$commits): void {
                $commits[] = 'failed';
            });

            if ($entity->get('name') !== 'Test 2') {
                return;
            }

            $Items->setTemporaryField($entity, 'temporary', 'value');

            throw $exception;
        });

        $caught = null;

        try {
            $Items->saveMany($items);
        } catch (Throwable $e) {
            $caught = $e;
        }

        $this->assertSame(
            $exception,
            $caught
        );

        $this->assertSame(
            0,
            $this->db->getSavePointLevel()
        );

        $this->assertFalse(
            $this->db->inTransaction()
        );

        $this->assertSame(
            0,
            $Items->find()->count()
        );

        $this->assertSame(
            [],
            $commits
        );

        foreach ($items as $item) {
            $this->assertNull(
                $item->id
            );

            $this->assertTrue(
                $item->isNew()
            );

            $this->assertFalse(
                $item->hasValue('temporary')
            );
        }
    }

    public function testSaveManyExceptionRollsBackNestedTransaction(): void
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

        $this->db->begin();

        $existing = $Items->newEntity([
            'name' => 'Existing',
        ]);

        $this->assertTrue(
            $Items->save($existing)
        );

        $commits = [];
        $this->db->afterCommit(static function() use (&$commits): void {
            $commits[] = 'outer';
        });

        $exception = new Error('Operation failed.');

        $Items->getEventManager()->on('ORM.afterSave', function(Event $event, Item $entity) use ($Items, $exception, &$commits): void {
            $this->db->afterCommit(static function() use (&$commits): void {
                $commits[] = 'failed';
            });

            if ($entity->get('name') !== 'Test 2') {
                return;
            }

            $Items->setTemporaryField($entity, 'temporary', 'value');

            throw $exception;
        });

        $caught = null;

        try {
            $Items->saveMany($items);
        } catch (Throwable $e) {
            $caught = $e;
        }

        $this->assertSame(
            $exception,
            $caught
        );

        $this->assertSame(
            1,
            $this->db->getSavePointLevel()
        );

        $this->assertTrue(
            $this->db->inTransaction()
        );

        $this->assertSame(
            1,
            $Items->find()->count()
        );

        $this->assertSame(
            [],
            $commits
        );

        foreach ($items as $item) {
            $this->assertNull(
                $item->id
            );

            $this->assertTrue(
                $item->isNew()
            );

            $this->assertFalse(
                $item->hasValue('temporary')
            );
        }

        $this->db->commit();

        $this->assertSame(
            ['outer'],
            $commits
        );

        $this->assertFalse(
            $this->db->inTransaction()
        );

        $this->assertTrue(
            $Items->exists(['name' => 'Existing'])
        );
    }

    public function testSaveManyInnerRollbackKeepsChangesDirty(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $items = $Items->newEntities([
            ['name' => 'First'],
            ['name' => 'Second'],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $this->db->begin();

        foreach ($items as $item) {
            $item->name = 'Outer';
        }

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $this->db->begin();

        foreach ($items as $item) {
            $item->name = 'Inner';
        }

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $this->db->rollback();
        $this->db->commit();

        foreach ($items as $item) {
            $this->assertNotNull(
                $item->id
            );
            $this->assertSame(
                'Outer',
                $Items->get($item->id)?->name
            );
            $this->assertSame(
                'Inner',
                $item->name
            );
            $this->assertSame(
                'Outer',
                $item->getOriginal('name')
            );
            $this->assertTrue(
                $item->isDirty('name')
            );
        }

        $this->assertTrue(
            $Items->saveMany($items)
        );

        foreach ($items as $item) {
            $this->assertNotNull(
                $item->id
            );
            $this->assertSame(
                'Inner',
                $Items->get($item->id)?->name
            );
            $this->assertFalse(
                $item->isDirty()
            );
        }
    }

    public function testSaveManyOuterRollbackAfterRepeatedSave(): void
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

        $this->db->begin();

        $this->assertTrue(
            $Items->saveMany($items)
        );
        $this->assertTrue(
            $Items->saveMany($items)
        );

        foreach ($items as $item) {
            $this->assertFalse(
                $item->isNew()
            );
        }

        $this->db->rollback();

        foreach ($items as $item) {
            $this->assertTrue(
                $item->isNew()
            );
            $this->assertFalse(
                $item->hasValue('id')
            );
        }

        $this->assertTrue(
            $Items->saveMany($items)
        );
        $this->assertSame(
            2,
            $Items->find()->count()
        );
    }

    public function testSaveManyWithoutRelatedKeepsChildrenDirty(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $Posts = $this->modelRegistry->use('Posts');

        $users = $Users->newEntities([
            ['name' => 'First', 'posts' => [['title' => 'First post']]],
            ['name' => 'Second', 'posts' => [['title' => 'Second post']]],
        ]);

        $this->assertTrue(
            $Users->saveMany($users, saveRelated: false)
        );
        $this->assertSame(
            0,
            $Posts->find()->count()
        );

        foreach ($users as $user) {
            $post = $user->posts[0];

            $this->assertFalse(
                $user->isDirty()
            );
            $this->assertTrue(
                $post->isNew()
            );
            $this->assertTrue(
                $post->isDirty()
            );
            $this->assertTrue(
                $Posts->save($post)
            );
        }

        $this->assertSame(
            2,
            $Posts->find()->count()
        );
    }

    public function testSaveOuterCommitWithoutCleaning(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $item = $Items->newEntity([
            'name' => 'Test',
        ]);

        $this->db->begin();

        $this->assertTrue(
            $Items->save($item, clean: false)
        );

        $id = $item->id;
        $this->db->commit();
        $this->db->begin();
        $this->db->rollback();

        $this->assertSame(
            $id,
            $item->id
        );

        $item->set('name', 'Updated');

        $this->assertTrue(
            $Items->save($item)
        );
        $this->assertSame(
            1,
            $Items->find()->count()
        );
        $this->assertNotNull(
            $id
        );
        $this->assertSame(
            'Updated',
            $Items->get($id)?->name
        );
    }

    public function testSaveOuterRollback(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $item = $Items->newEntity([
            'name' => 'Test',
        ]);

        $this->db->begin();

        $this->assertTrue(
            $Items->save($item)
        );

        $id = $item->id;
        $this->db->rollback();

        $this->assertTrue(
            $item->isNew()
        );
        $this->assertFalse(
            $item->hasValue('id')
        );

        $this->db->insert()
            ->into('items')
            ->values([
                [
                    'id' => $id,
                    'name' => 'Other',
                ],
            ])
            ->execute();

        $this->assertTrue(
            $Items->save($item)
        );
        $this->assertSame(
            ['Other', 'Test'],
            $Items->find(orderBy: ['id' => 'ASC'])
                ->all()
                ->map(static fn(Entity $entity): string => $entity->name)
                ->toArray()
        );
    }

    public function testSaveRelatedExceptionRollsBack(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $Posts = $this->modelRegistry->use('Posts');

        $user = $Users->newEntity([
            'name' => 'User',
            'posts' => [
                [
                    'title' => 'First',
                ],
                [
                    'title' => 'Fail',
                ],
            ],
        ]);

        $exception = new RuntimeException('Related save failed.');

        $Posts->getEventManager()->on('ORM.afterSave', static function(Event $event, Entity $entity) use ($exception): void {
            if ($entity->get('title') === 'Fail') {
                throw $exception;
            }
        });

        $caught = null;

        try {
            $Users->save($user);
        } catch (Throwable $e) {
            $caught = $e;
        }

        $this->assertSame(
            $exception,
            $caught
        );

        $this->assertSame(
            0,
            $this->db->getSavePointLevel()
        );

        $this->assertFalse(
            $this->db->inTransaction()
        );

        $this->assertSame(
            0,
            $Users->find()->count()
        );

        $this->assertSame(
            0,
            $Posts->find()->count()
        );

        $this->assertNull(
            $user->id
        );

        $this->assertTrue(
            $user->isNew()
        );

        foreach ($user->posts as $post) {
            $this->assertNull(
                $post->id
            );

            $this->assertNull(
                $post->user_id
            );

            $this->assertTrue(
                $post->isNew()
            );

        }
    }

    public function testSaveRelatedInnerRollbackKeepsChildNew(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $Posts = $this->modelRegistry->use('Posts');
        $user = $Users->newEntity(['name' => 'Test']);

        $this->db->begin();

        $this->assertTrue(
            $Users->save($user)
        );

        $this->db->begin();

        $post = $Posts->newEntity([
            'user_id' => $user->id,
            'title' => 'Test',
        ]);
        $user->set('posts', [$post]);

        $this->assertTrue(
            $Posts->save($post)
        );

        $this->db->rollback();
        $this->db->commit();

        $this->assertTrue(
            $post->isNew()
        );
        $this->assertTrue(
            $post->isDirty()
        );
        $this->assertFalse(
            $post->hasValue('id')
        );
        $this->assertSame(
            0,
            $Posts->find()->count()
        );
        $this->assertTrue(
            $Posts->save($post)
        );
        $this->assertSame(
            1,
            $Posts->find()->count()
        );
    }

    public function testSaveRelatedInnerRollbackPreservesOuterForeignKey(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $Posts = $this->modelRegistry->use('Posts');
        $post = $Posts->newEntity([
            'title' => 'Post',
            'user_id' => null,
        ]);

        $this->assertTrue(
            $Posts->save($post)
        );

        $postId = $post->id;
        $user = $Users->newEntity([
            'name' => 'Outer',
        ]);
        $user->set('posts', [$post]);
        $this->db->begin();

        $this->assertTrue(
            $Users->save($user)
        );

        $userId = $user->id;
        $innerUser = $Users->newEntity([
            'name' => 'Inner',
        ]);
        $post->set('user', $innerUser);
        $this->db->begin();

        $this->assertTrue(
            $Posts->save($post)
        );
        $this->assertSame(
            $innerUser->id,
            $post->user_id
        );

        $this->db->rollback();

        $this->assertNotNull(
            $postId
        );
        $this->assertNotNull(
            $userId
        );
        $this->assertSame(
            $userId,
            $user->id
        );
        $this->assertSame(
            $userId,
            $post->user_id
        );
        $this->assertSame(
            $userId,
            $Posts->get($postId)?->user_id
        );
        $this->assertFalse(
            $innerUser->has('id')
        );

        $this->db->rollback();

        $this->assertNull(
            $post->user_id
        );
        $this->assertSame(
            $postId,
            $post->id
        );

        $saved = $Posts->get($postId);

        $this->assertInstanceOf(
            Post::class,
            $saved
        );
        $this->assertNull(
            $saved->user_id
        );
        $this->assertFalse(
            $user->has('id')
        );
    }

    public function testSaveRelatedOuterRollback(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $user = $Users->newEntity([
            'name' => 'Test',
            'posts' => [
                [
                    'title' => 'Post',
                ],
            ],
        ]);
        $post = $user->posts[0];

        $this->db->begin();

        $this->assertTrue(
            $Users->save($user)
        );

        $this->db->rollback();

        $this->assertTrue(
            $user->isNew()
        );
        $this->assertTrue(
            $post->isNew()
        );
        $this->assertFalse(
            $user->hasValue('id')
        );
        $this->assertFalse(
            $post->hasValue('id')
        );
        $this->assertFalse(
            $post->hasValue('user_id')
        );

        $this->assertTrue(
            $Users->save($user)
        );
        $this->assertNotNull(
            $user->id
        );
        $this->assertNotNull(
            $post->id
        );
        $this->assertSame(
            $user->id,
            $this->modelRegistry->use('Posts')->get($post->id)?->user_id
        );
    }

    public function testSaveRelatedWithoutCleaning(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $user = $Users->newEntity([
            'name' => 'Test',
            'posts' => [['title' => 'Test']],
        ]);

        $this->assertTrue(
            $Users->save($user, clean: false)
        );
        $this->assertTrue(
            $user->isDirty()
        );
        $this->assertTrue(
            $user->posts[0]->isDirty()
        );
    }

    public function testSaveWithoutRelatedKeepsChildrenDirty(): void
    {
        $Users = $this->modelRegistry->use('Users');
        $Posts = $this->modelRegistry->use('Posts');
        $existing = $Posts->newEntity(['title' => 'Original']);

        $this->assertTrue(
            $Posts->save($existing)
        );

        $existing->set('title', 'Updated');
        $post = $Posts->newEntity(['title' => 'New']);
        $user = $Users->newEntity(['name' => 'Test']);
        $user->set('posts', [$existing, $post]);

        $this->assertTrue(
            $Users->save($user, saveRelated: false)
        );
        $this->assertFalse(
            $user->isDirty()
        );
        $this->assertTrue(
            $existing->isDirty('title')
        );
        $this->assertNotNull(
            $existing->id
        );
        $this->assertSame(
            'Original',
            $Posts->get($existing->id)?->title
        );
        $this->assertTrue(
            $post->isNew()
        );
        $this->assertTrue(
            $post->isDirty()
        );
        $this->assertSame(
            1,
            $Posts->find()->count()
        );
        $this->assertTrue(
            $Posts->saveMany([$existing, $post])
        );
        $this->assertSame(
            2,
            $Posts->find()->count()
        );
        $this->assertSame(
            'Updated',
            $Posts->get($existing->id)->title
        );
    }

    public function testSetTemporaryFieldRollback(): void
    {
        $Model = $this->modelRegistry->use('Test');
        $entity = new Entity([
            'field' => 1,
        ]);
        $entity->set('field', 2);
        $original = clone $entity;
        $this->db->begin();

        $this->assertSame(
            $Model,
            $Model->setTemporaryField($entity, 'field', 3)
        );

        $this->db->rollback();

        $this->assertEquals(
            $original,
            $entity
        );
    }

    public function testSetTemporaryFieldRollbackKeepsErrors(): void
    {
        $Model = $this->modelRegistry->use('Test');
        $entity = new Entity([
            'field' => 1,
        ]);
        $this->db->begin();
        $Model->setTemporaryField($entity, 'field', 2);
        $entity->setError('field', 'Invalid');
        $this->db->rollback();

        $this->assertSame(
            1,
            $entity->get('field')
        );
        $this->assertSame(
            ['Invalid'],
            $entity->getError('field')
        );
    }

    public function testSetTemporaryFieldRollbackMarkedDirty(): void
    {
        $Model = $this->modelRegistry->use('Test');
        $entity = new Entity([
            'field' => 1,
        ]);
        $entity->setDirty('field');
        $original = clone $entity;
        $this->db->begin();
        $Model->setTemporaryField($entity, 'field', 2);
        $this->db->rollback();

        $this->assertEquals(
            $original,
            $entity
        );
    }

    public function testSetTemporaryFieldRollbackMutation(): void
    {
        $Model = $this->modelRegistry->use('Test');
        $entity = new MockEntity([
            'decimal' => 1.234,
        ]);
        $original = clone $entity;
        $this->db->begin();
        $Model->setTemporaryField($entity, 'decimal', 2.345);
        $this->db->rollback();

        $this->assertEquals(
            $original,
            $entity
        );
    }

    public function testSetTemporaryFieldRollbackNested(): void
    {
        $Model = $this->modelRegistry->use('Test');
        $entity = new Entity([
            'field' => 1,
        ]);
        $original = clone $entity;
        $this->db->begin();
        $Model->setTemporaryField($entity, 'field', 2);
        $outer = clone $entity;
        $this->db->begin();
        $Model->setTemporaryField($entity, 'field', 3);
        $this->db->rollback();

        $this->assertEquals(
            $outer,
            $entity
        );

        $this->db->rollback();

        $this->assertEquals(
            $original,
            $entity
        );
    }

    public function testSetTemporaryFieldRollbackNew(): void
    {
        $Model = $this->modelRegistry->use('Test');
        $entity = new Entity();
        $this->db->begin();
        $Model->setTemporaryField($entity, 'field', 1);
        $entity->set('other', 2);
        $this->db->rollback();

        $this->assertFalse(
            $entity->has('field')
        );
        $this->assertFalse(
            $entity->isDirty('field')
        );
        $this->assertSame(
            2,
            $entity->get('other')
        );
        $this->assertTrue(
            $entity->isDirty('other')
        );
    }

    public function testSetTemporaryFieldRollbackNull(): void
    {
        $Model = $this->modelRegistry->use('Test');
        $entity = new Entity([
            'field' => null,
        ]);
        $original = clone $entity;
        $this->db->begin();
        $Model->setTemporaryField($entity, 'field', 1);
        $this->db->rollback();

        $this->assertEquals(
            $original,
            $entity
        );
    }

    public function testSetTemporaryFieldWithoutTransaction(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessage('Cannot set a temporary field outside a transaction.');

        $Items = $this->modelRegistry->use('Items');
        $item = $Items->newEntity([
            'name' => 'Test',
        ]);

        $Items->setTemporaryField($item, 'name', 'Updated');
    }
}
