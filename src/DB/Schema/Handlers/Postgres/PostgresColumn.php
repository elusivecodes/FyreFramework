<?php
declare(strict_types=1);

namespace Fyre\DB\Schema\Handlers\Postgres;

use Fyre\DB\QueryLiteral;
use Fyre\DB\Schema\Column;
use Fyre\DB\TypeParser;
use Override;

/**
 * Provides PostgreSQL column metadata.
 */
class PostgresColumn extends Column
{
    /**
     * @var array<string, string>
     */
    #[Override]
    protected static array $types = [
        'bigint' => 'integer',
        'boolean' => 'boolean',
        'bytea' => 'binary',
        'date' => 'date',
        'datetime' => 'datetime',
        'double precision' => 'float',
        'integer' => 'integer',
        'json' => 'json',
        'jsonb' => 'json',
        'numeric' => 'decimal',
        'real' => 'float',
        'smallint' => 'integer',
        'text' => 'text',
        'time without time zone' => 'time',
        'timestamp without time zone' => 'datetime-fractional',
        'timestamp with time zone' => 'datetime-timezone',
    ];

    /**
     * Constructs a PostgresColumn.
     *
     * @param PostgresTable $table The PostgresTable.
     * @param TypeParser $typeParser The TypeParser.
     * @param string $name The column name.
     * @param string $type The column type.
     * @param int|null $length The column length.
     * @param int|null $precision The column precision.
     * @param bool $nullable Whether the column is nullable.
     * @param bool|float|int|QueryLiteral|string|null $default The column default value.
     * @param string|null $comment The column comment.
     * @param bool $autoIncrement Whether the column is auto-incrementing.
     */
    public function __construct(
        PostgresTable $table,
        TypeParser $typeParser,
        string $name,
        string $type,
        int|null $length = null,
        int|null $precision = null,
        int|null $scale = null,
        int|null $fractionalSeconds = null,
        bool $nullable = false,
        bool|float|int|QueryLiteral|string|null $default = null,
        string|null $comment = '',
        bool $autoIncrement = false,
    ) {
        parent::__construct(
            $table,
            $typeParser,
            $name,
            $type,
            $length,
            $precision,
            $scale,
            $fractionalSeconds,
            $nullable,
            false,
            $default,
            $comment,
            $autoIncrement
        );
    }
}
