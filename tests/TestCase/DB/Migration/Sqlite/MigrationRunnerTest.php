<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Migration\Sqlite;

use Fyre\DB\Exceptions\DbException;
use Fyre\DB\Migration\MigrationRunner;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

use function array_column;

final class MigrationRunnerTest extends TestCase
{
    use SqliteConnectionTrait;

    public function testRollbackRetainsMissingMigrationHistory(): void
    {
        $this->migrationRunner->migrate();

        $migrations = $this->migrationRunner->getMigrations();
        unset($migrations['3_Test3']);

        $property = new ReflectionProperty(MigrationRunner::class, 'migrations');
        $property->setValue($this->migrationRunner, $migrations);

        try {
            $this->migrationRunner->rollback(steps: 1);
            $this->fail('Expected rollback to fail for a missing migration implementation.');
        } catch (DbException $e) {
            $this->assertSame(
                'Migration implementation `3_Test3` could not be found.',
                $e->getMessage()
            );
        }

        $this->assertSame(
            ['3_Test3', '2_Test2', '1_Test1'],
            array_column($this->migrationRunner->getHistory()->all(), 'migration')
        );

        $this->schema->clear();
        $this->assertTrue(
            $this->schema->hasTable('test3')
        );
    }
}
