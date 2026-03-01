<?php
declare(strict_types=1);

namespace Fyre\DB\Handlers\Sqlite;

use Fyre\DB\QueryGenerator;
use Override;

use function array_filter;
use function array_keys;
use function array_map;
use function array_merge;
use function array_unique;
use function implode;
use function in_array;

/**
 * Compiles SQLite SQL for query builders.
 */
class SqliteQueryGenerator extends QueryGenerator
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function buildOnConflict(array $conflictKeys, array $values, array $excludeUpdateKeys): string
    {
        $excludeUpdateKeys = array_merge($conflictKeys, $excludeUpdateKeys) |> array_unique(...);

        $query = ' ON CONFLICT';
        $query .= ' ('.implode(', ', $conflictKeys).')';
        $query .= ' DO UPDATE SET ';

        $columns = array_filter(
            array_keys($values[0] ?? []),
            static fn(int|string $column): bool => !in_array($column, $excludeUpdateKeys, true)
        );

        $columns = array_map(
            static fn(int|string $column): string => $column.' = excluded.'.$column,
            $columns
        );

        $query .= implode(', ', $columns);

        return $query;
    }
}
