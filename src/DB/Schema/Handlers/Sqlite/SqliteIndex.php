<?php
declare(strict_types=1);

namespace Fyre\DB\Schema\Handlers\Sqlite;

use Fyre\DB\Schema\Index;

/**
 * Provides SQLite index metadata.
 */
class SqliteIndex extends Index
{
    /**
     * Constructs a SqliteIndex.
     *
     * @param SqliteTable $table The SqliteTable.
     * @param string $name The index name.
     * @param string[] $columns The index columns.
     * @param bool $unique Whether the index is unique.
     * @param bool $primary Whether the index is primary.
     */
    public function __construct(
        SqliteTable $table,
        string $name,
        array $columns = [],
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
