<?php
declare(strict_types=1);

namespace Fyre\DB\Forge\Handlers\Mysql;

use Fyre\DB\Forge\Index;

/**
 * Defines a MySQL index for DDL operations.
 */
class MysqlIndex extends Index
{
    /**
     * Constructs a MysqlIndex.
     *
     * @param MysqlTable $table The MysqlTable.
     * @param string $name The index name.
     * @param string|string[] $columns The index columns.
     * @param bool $unique Whether the index is unique.
     * @param bool $primary Whether the index is primary.
     * @param string $type The index type.
     */
    public function __construct(
        MysqlTable $table,
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
            $primary || $name === 'PRIMARY',
            $type
        );
    }
}
