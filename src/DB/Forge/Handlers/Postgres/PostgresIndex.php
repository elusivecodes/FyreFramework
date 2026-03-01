<?php
declare(strict_types=1);

namespace Fyre\DB\Forge\Handlers\Postgres;

use Fyre\DB\Forge\Index;

/**
 * Defines a PostgreSQL index for DDL operations.
 */
class PostgresIndex extends Index
{
    /**
     * Constructs a PostgresIndex.
     *
     * @param PostgresTable $table The PostgresTable.
     * @param string $name The index name.
     * @param string|string[] $columns The index columns.
     * @param bool $unique Whether the index is unique.
     * @param bool $primary Whether the index is primary.
     * @param string $type The index type.
     */
    public function __construct(
        PostgresTable $table,
        string $name,
        array|string $columns,
        bool $unique = false,
        bool $primary = false,
        string $type = 'btree',
    ) {
        parent::__construct(
            $table,
            $name,
            $columns,
            $unique,
            $primary,
            $type
        );
    }
}
