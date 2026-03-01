<?php
declare(strict_types=1);

namespace Fyre\DB\Forge\Handlers\Sqlite;

use Fyre\DB\Forge\Index;

/**
 * Defines a SQLite index for DDL operations.
 */
class SqliteIndex extends Index
{
    /**
     * Constructs a SqliteIndex.
     *
     * @param SqliteTable $table The SqliteTable.
     * @param string $name The index name.
     * @param string|string[] $columns The index columns.
     * @param bool $unique Whether the index is unique.
     * @param bool $primary Whether the index is primary.
     */
    public function __construct(
        SqliteTable $table,
        string $name,
        array|string $columns,
        bool $unique = false,
        bool $primary = false,
    ) {
        parent::__construct(
            $table,
            $name,
            $columns,
            $unique,
            $primary
        );
    }
}
