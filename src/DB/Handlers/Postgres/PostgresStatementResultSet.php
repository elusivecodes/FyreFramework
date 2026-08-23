<?php
declare(strict_types=1);

namespace Fyre\DB\Handlers\Postgres;

use Fyre\DB\StatementResultSet;
use Override;

/**
 * Provides a PostgreSQL {@see StatementResultSet} implementation with native type mapping.
 */
class PostgresStatementResultSet extends StatementResultSet
{
    #[Override]
    protected static array $types = [
        'bool' => 'boolean',
        'date' => 'date',
        'float4' => 'float',
        'float8' => 'float',
        'int2' => 'integer',
        'int4' => 'integer',
        'int8' => 'integer',
        'money' => 'decimal',
        'numeric' => 'decimal',
        'time' => 'time',
        'timestamp' => 'datetime',
        'timestamptz' => 'datetime-timezone',
    ];
}
