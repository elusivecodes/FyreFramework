<?php
declare(strict_types=1);

namespace Fyre\DB\Handlers\Postgres;

use Fyre\DB\QueryGenerator;
use Fyre\DB\ValueBinder;
use Override;

use function array_filter;
use function array_keys;
use function array_map;
use function array_merge;
use function array_unique;
use function implode;
use function in_array;
use function is_numeric;
use function str_replace;

/**
 * Compiles PostgreSQL SQL for query builders.
 */
class PostgresQueryGenerator extends QueryGenerator
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
            static fn(int|string $column): string => $column.' = EXCLUDED.'.$column,
            $columns
        );

        $query .= implode(', ', $columns);

        return $query;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function buildSelectFields(array $fields, ValueBinder|null $binder): array
    {
        return array_map(
            function(int|string $key, mixed $value) use ($binder): string {
                $value = $this->parseExpression($value, $binder, false);

                if (is_numeric($key)) {
                    return $value;
                }

                return $value.' AS "'.str_replace('"', '""', $key).'"';
            },
            array_keys($fields),
            $fields
        );
    }
}
