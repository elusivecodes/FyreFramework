<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Migration;

use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Exceptions\DbException;
use Fyre\DB\Migration\Migration;
use Fyre\DB\Migration\MigrationHistory;
use Fyre\DB\Migration\MigrationRunner;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Migrations\Migration_1_Test1;
use Tests\Mock\Migrations\Migration_2_Test2;
use Tests\Mock\Migrations\Migration_3_Test3;
use Tests\TestCase\DB\Migration\Mysql\MysqlConnectionTrait;

use function class_uses;

final class MigrationRunnerTest extends TestCase
{
    use MysqlConnectionTrait;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(MigrationRunner::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(MigrationHistory::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Migration::class)
        );
    }

    public function testGetMigrations(): void
    {
        $this->assertArraysAreIdentical(
            [
                '1_Test1' => Migration_1_Test1::class,
                '2_Test2' => Migration_2_Test2::class,
                '3_Test3' => Migration_3_Test3::class,
            ],
            $this->migrationRunner->getMigrations()
        );
    }

    public function testGetNamespaces(): void
    {
        $this->assertArraysAreIdentical(
            [
                'Tests\Mock\Migrations\\',
            ],
            $this->migrationRunner->getNamespaces()
        );
    }

    public function testGetPendingMigrations(): void
    {
        $this->assertArraysAreIdentical(
            [
                '1_Test1' => Migration_1_Test1::class,
                '2_Test2' => Migration_2_Test2::class,
                '3_Test3' => Migration_3_Test3::class,
            ],
            $this->migrationRunner->getPendingMigrations()
        );
    }

    public function testGetPendingMigrationsAfterMigrate(): void
    {
        $this->migrationRunner->migrate();

        $this->assertArraysAreIdentical(
            [],
            $this->migrationRunner->getPendingMigrations()
        );
    }

    public function testGetRollbackMigrations(): void
    {
        $this->migrationRunner->migrate();

        $this->assertArraysAreIdentical(
            [
                '3_Test3' => Migration_3_Test3::class,
                '2_Test2' => Migration_2_Test2::class,
                '1_Test1' => Migration_1_Test1::class,
            ],
            $this->migrationRunner->getRollbackMigrations()
        );
    }

    public function testGetRollbackMigrationsBatches(): void
    {
        $this->migrationRunner->migrate();
        $this->migrationRunner->rollback(steps: 1);
        $this->migrationRunner->migrate();

        $this->assertArraysAreIdentical(
            [
                '3_Test3' => Migration_3_Test3::class,
            ],
            $this->migrationRunner->getRollbackMigrations()
        );
    }

    public function testGetRollbackMigrationsMissing(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionMessageIs('Migration implementation `0_Missing` could not be found.');

        $this->migrationRunner->getHistory()->add('0_Missing', 1);
        $this->migrationRunner->getRollbackMigrations();
    }

    public function testGetRollbackMigrationsSteps(): void
    {
        $this->migrationRunner->migrate();

        $this->assertArraysAreIdentical(
            [
                '3_Test3' => Migration_3_Test3::class,
                '2_Test2' => Migration_2_Test2::class,
            ],
            $this->migrationRunner->getRollbackMigrations(null, 2)
        );
    }

    public function testGetStatus(): void
    {
        $history = $this->migrationRunner->getHistory();
        $history->add('0_Missing', 1);
        $history->add('1_Test1', 2);

        $this->assertArraysAreIdentical(
            [
                [
                    'migration' => '0_Missing',
                    'status' => 'missing',
                    'batch' => 1,
                ],
                [
                    'migration' => '1_Test1',
                    'status' => 'up',
                    'batch' => 2,
                ],
                [
                    'migration' => '2_Test2',
                    'status' => 'down',
                    'batch' => null,
                ],
                [
                    'migration' => '3_Test3',
                    'status' => 'down',
                    'batch' => null,
                ],
            ],
            $this->migrationRunner->getStatus()
        );
    }

    public function testMigrate(): void
    {
        $this->assertSame(
            $this->migrationRunner,
            $this->migrationRunner->migrate()
        );

        $this->assertSame(
            3,
            $this->migrationRunner->getLastMigrationCount()
        );

        $this->schema->clear();

        $this->assertTrue(
            $this->schema->hasTable('test1')
        );

        $this->assertTrue(
            $this->schema->hasTable('test2')
        );

        $this->assertTrue(
            $this->schema->hasTable('test3')
        );

        $this->assertSame(
            0,
            $this->db
                ->select()
                ->from('fyre__locks')
                ->execute()
                ->count()
        );
    }

    public function testMigrateFromVersion(): void
    {
        $this->migrationRunner->migrate();
        $this->migrationRunner->rollback();
        $this->migrationRunner->migrate();

        $this->schema->clear();

        $this->assertTrue(
            $this->schema->hasTable('test1')
        );

        $this->assertTrue(
            $this->schema->hasTable('test2')
        );

        $this->assertTrue(
            $this->schema->hasTable('test3')
        );
    }

    public function testMigrateLocked(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionMessageIs('Migration lock could not be acquired.');

        $this->migrationRunner->migrate();
        $this->migrationRunner->rollback();

        $lock = $this->db->lock('fyre__migrations');

        $this->assertTrue(
            $lock->acquire()
        );

        $this->migrationRunner->migrate();
    }

    public function testRollback(): void
    {
        $this->migrationRunner->migrate();

        $this->assertSame(
            $this->migrationRunner,
            $this->migrationRunner->rollback()
        );

        $this->assertSame(
            3,
            $this->migrationRunner->getLastMigrationCount()
        );

        $this->schema->clear();

        $this->assertFalse(
            $this->schema->hasTable('test1')
        );

        $this->assertFalse(
            $this->schema->hasTable('test2')
        );

        $this->assertFalse(
            $this->schema->hasTable('test3')
        );
    }

    public function testRollbackLocked(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionMessageIs('Migration lock could not be acquired.');

        $this->migrationRunner->migrate();

        $lock = $this->db->lock('fyre__migrations');

        $this->assertTrue(
            $lock->acquire()
        );

        $this->migrationRunner->rollback();
    }

    public function testRollbackSteps(): void
    {
        $this->migrationRunner->migrate();
        $this->migrationRunner->rollback(steps: 2);

        $this->schema->clear();

        $this->assertTrue(
            $this->schema->hasTable('test1')
        );

        $this->assertFalse(
            $this->schema->hasTable('test2')
        );

        $this->assertFalse(
            $this->schema->hasTable('test3')
        );
    }

    public function testSetLockExpires(): void
    {
        $this->assertSame(
            $this->migrationRunner,
            $this->migrationRunner->setLockExpires(600)
        );
    }

    public function testSetLockExpiresInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Migration lock expiration must be greater than 0.');

        $this->migrationRunner->setLockExpires(0);
    }
}
