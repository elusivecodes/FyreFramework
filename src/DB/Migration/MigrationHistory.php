<?php
declare(strict_types=1);

namespace Fyre\DB\Migration;

use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Connection;
use Fyre\DB\Forge\ForgeRegistry;
use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;

/**
 * Stores and queries migration history.
 *
 * Tracks which migrations have been applied to a connection.
 */
class MigrationHistory
{
    use DebugTrait;

    protected static string $table = 'migrations';

    /**
     * Constructs a MigrationHistory.
     *
     * @param Connection $connection The Connection.
     * @param ForgeRegistry $forgeRegistry The ForgeRegistry.
     */
    public function __construct(
        protected Connection $connection,
        protected ForgeRegistry $forgeRegistry
    ) {
        $this->check();
    }

    /**
     * Adds a migration to the history.
     *
     * @param string $name The migration name.
     * @param int $batch The batch number.
     */
    public function add(string $name, int $batch): void
    {
        $this->connection
            ->insert()
            ->into(static::$table)
            ->values([
                [
                    'batch' => $batch,
                    'migration' => $name,
                ],
            ])
            ->execute();
    }

    /**
     * Returns the migration history.
     *
     * @return array<string, mixed>[] The migration history.
     */
    public function all(): array
    {
        return $this->connection
            ->select()
            ->from(static::$table)
            ->orderBy([
                'batch' => 'DESC',
                'id' => 'DESC',
            ])
            ->execute()
            ->all();
    }

    /**
     * Deletes a migration from the history.
     *
     * @param string $name The migration name.
     */
    public function delete(string $name): void
    {
        $this->connection
            ->delete()
            ->from(static::$table)
            ->where([
                'migration' => $name,
            ])
            ->execute();
    }

    /**
     * Returns the next batch number.
     *
     * @return int The next batch number.
     */
    public function getNextBatch(): int
    {
        $lastBatch = $this->connection
            ->select([
                'last_batch' => 'MAX(batch)',
            ])
            ->from(static::$table)
            ->execute()
            ->fetch()['last_batch'] ?? 0;

        return $lastBatch + 1;
    }

    /**
     * Checks the migration schema.
     *
     * Ensures the migrations table exists with the expected columns and indexes.
     */
    protected function check(): void
    {
        $table = $this->forgeRegistry->use($this->connection)
            ->build(static::$table)
            ->clear()
            ->addColumn('id', [
                'type' => IntegerType::class,
                'autoIncrement' => true,
            ])
            ->addColumn('batch', [
                'type' => IntegerType::class,
            ])
            ->addColumn('migration', [
                'type' => StringType::class,
            ])
            ->addColumn('timestamp', [
                'type' => DateTimeType::class,
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->setPrimaryKey('id')
            ->addIndex('batch')
            ->addIndex('migration', [
                'unique' => true,
            ])
            ->execute();
    }
}
