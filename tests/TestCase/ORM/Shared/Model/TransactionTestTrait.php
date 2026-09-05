<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use Error;
use Fyre\DB\Exceptions\DbException;
use Fyre\Event\Event;
use Fyre\Event\EventManager;
use Fyre\Log\LogManager;
use Fyre\ORM\Entity;
use RuntimeException;
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

        $Items->getEventManager()->on('ORM.afterDelete', function(Event $event, Entity $entity) use ($exception, &$commits): void {
            $this->db->afterCommit(static function() use (&$commits): void {
                $commits[] = 'failed';
            });

            $entity->set('temporary', 'value', temporary: true);

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

        $this->assertSame(
            [],
            $item->getTemporaryFields()
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

        $Items->getEventManager()->on('ORM.afterDelete', function(Event $event, Entity $entity) use ($exception, &$commits): void {
            $this->db->afterCommit(static function() use (&$commits): void {
                $commits[] = 'failed';
            });

            $entity->set('temporary', 'value', temporary: true);

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

        $this->assertSame(
            [],
            $item->getTemporaryFields()
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

        $Items->getEventManager()->on('ORM.afterDelete', function(Event $event, Entity $entity) use ($exception, &$commits): void {
            $this->db->afterCommit(static function() use (&$commits): void {
                $commits[] = 'failed';
            });

            if ($entity->get('name') !== 'Test 2') {
                return;
            }

            $entity->set('temporary', 'value', temporary: true);

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

            $this->assertSame(
                [],
                $item->getTemporaryFields()
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

        $Items->getEventManager()->on('ORM.afterDelete', function(Event $event, Entity $entity) use ($exception, &$commits): void {
            $this->db->afterCommit(static function() use (&$commits): void {
                $commits[] = 'failed';
            });

            if ($entity->get('name') !== 'Test 2') {
                return;
            }

            $entity->set('temporary', 'value', temporary: true);

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

            $this->assertSame(
                [],
                $item->getTemporaryFields()
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

            $this->assertSame(
                [],
                $item->getTemporaryFields()
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

        $Items->getEventManager()->on('ORM.afterSave', function(Event $event, Entity $entity) use ($exception, &$commits): void {
            $this->db->afterCommit(static function() use (&$commits): void {
                $commits[] = 'failed';
            });

            $entity->set('temporary', 'value', temporary: true);

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

        $this->assertSame(
            [],
            $item->getTemporaryFields()
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

        $Items->getEventManager()->on('ORM.afterSave', function(Event $event, Entity $entity) use ($exception, &$commits): void {
            $this->db->afterCommit(static function() use (&$commits): void {
                $commits[] = 'failed';
            });

            $entity->set('temporary', 'value', temporary: true);

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

        $this->assertSame(
            [],
            $item->getTemporaryFields()
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

        $Items->getEventManager()->on('ORM.afterSave', function(Event $event, Entity $entity) use ($exception, &$commits): void {
            $this->db->afterCommit(static function() use (&$commits): void {
                $commits[] = 'failed';
            });

            if ($entity->get('name') !== 'Test 2') {
                return;
            }

            $entity->set('temporary', 'value', temporary: true);

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

            $this->assertSame(
                [],
                $item->getTemporaryFields()
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

        $Items->getEventManager()->on('ORM.afterSave', function(Event $event, Entity $entity) use ($exception, &$commits): void {
            $this->db->afterCommit(static function() use (&$commits): void {
                $commits[] = 'failed';
            });

            if ($entity->get('name') !== 'Test 2') {
                return;
            }

            $entity->set('temporary', 'value', temporary: true);

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

            $this->assertSame(
                [],
                $item->getTemporaryFields()
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

        $this->assertSame(
            [],
            $user->getTemporaryFields()
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

            $this->assertSame(
                [],
                $post->getTemporaryFields()
            );
        }
    }
}
