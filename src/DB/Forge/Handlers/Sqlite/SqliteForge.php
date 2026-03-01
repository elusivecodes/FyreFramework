<?php
declare(strict_types=1);

namespace Fyre\DB\Forge\Handlers\Sqlite;

use Fyre\DB\Forge\Forge;
use Override;

/**
 * Provides a SQLite {@see Forge} implementation.
 */
class SqliteForge extends Forge
{
    /**
     * {@inheritDoc}
     *
     * @return SqliteTable The new SqliteTable instance.
     */
    #[Override]
    public function build(string $tableName, array $options = []): SqliteTable
    {
        return $this->container->build(SqliteTable::class, [
            'forge' => $this,
            'name' => $tableName,
            ...$options,
        ]);
    }

    /**
     * {@inheritDoc}
     *
     * @return SqliteQueryGenerator The SqliteQueryGenerator instance.
     */
    #[Override]
    public function generator(): SqliteQueryGenerator
    {
        /** @var SqliteQueryGenerator */
        return $this->generator ??= $this->container->build(SqliteQueryGenerator::class, [
            'forge' => $this,
        ]);
    }
}
