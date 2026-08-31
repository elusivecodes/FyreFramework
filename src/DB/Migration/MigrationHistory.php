<?php
declare(strict_types=1);

namespace Fyre\DB\Migration;

use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\Connection;
use Fyre\DB\Expressions\AggregateExpression;
use Fyre\DB\Forge\Presets\MigrationsPreset;
use Fyre\DB\Query;

/**
 * Stores and queries migration history.
 *
 * Tracks which migrations have been applied to a connection.
 */
class MigrationHistory
{
    use DebugTrait;

    protected bool $checked = false;

    /**
     * Constructs a MigrationHistory.
     *
     * @param Connection $connection The Connection.
     * @param MigrationsPreset $migrationsPreset The MigrationsPreset.
     */
    public function __construct(
        protected Connection $connection,
        protected MigrationsPreset $migrationsPreset
    ) {}

    /**
     * Adds a migration to the history.
     *
     * @param string $name The migration name.
     * @param int $batch The batch number.
     */
    public function add(string $name, int $batch): void
    {
        $this->checkTable();

        $this->connection
            ->insert()
            ->into(MigrationsPreset::TABLE)
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
            ->from(MigrationsPreset::TABLE)
            ->orderBy([
                'batch' => 'DESC',
                'id' => 'DESC',
            ])
            ->execute()
            ->all();
    }

    /**
     * Checks the migration history table.
     *
     * Ensures the migrations table exists with the expected columns and indexes.
     */
    public function checkTable(): void
    {
        if ($this->checked) {
            return;
        }

        $this->migrationsPreset->check($this->connection);

        $this->checked = true;
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
            ->from(MigrationsPreset::TABLE)
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
                'last_batch' => static fn(Query $query): AggregateExpression => $query->func()
                    ->max('batch'),
            ])
            ->from(MigrationsPreset::TABLE)
            ->execute()
            ->fetch()['last_batch'] ?? 0;

        return $lastBatch + 1;
    }

    /**
     * Checks whether the migration history table exists.
     *
     * @return bool Whether the migration history table exists.
     */
    protected function hasTable(): bool
    {
        return $this->migrationsPreset->exists($this->connection);
    }
}
