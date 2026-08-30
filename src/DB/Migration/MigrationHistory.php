<?php
declare(strict_types=1);

namespace Fyre\DB\Migration;

use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Connection;
use Fyre\DB\Forge\ForgeRegistry;
use Fyre\DB\Schema\SchemaRegistry;
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

    protected bool $checked = false;

    /**
     * Constructs a MigrationHistory.
     *
     * @param Connection $connection The Connection.
     * @param ForgeRegistry $forgeRegistry The ForgeRegistry.
     * @param SchemaRegistry $schemaRegistry The SchemaRegistry.
     */
    public function __construct(
        protected Connection $connection,
        protected ForgeRegistry $forgeRegistry,
        protected SchemaRegistry $schemaRegistry
    ) {}

    /**
     * Adds a migration to the history.
     *
     * @param string $name The migration name.
     * @param int $batch The batch number.
     */
    public function add(string $name, int $batch): void
    {
        $this->check();

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
        if (!$this->hasTable()) {
            return [];
        }

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
        if (!$this->hasTable()) {
            return;
        }

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
        if (!$this->hasTable()) {
            return 1;
        }

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
        if ($this->checked) {
            return;
        }

        $this->forgeRegistry->use($this->connection)
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

        $this->checked = true;
    }

    /**
     * Checks whether the migration history table exists.
     *
     * @return bool Whether the migration history table exists.
     */
    protected function hasTable(): bool
    {
        return $this->schemaRegistry
            ->use($this->connection)
            ->hasTable(static::$table);
    }
}
