<?php
declare(strict_types=1);

namespace Fyre\DB\Forge\Handlers\Postgres;

use Fyre\DB\Forge\Column;
use Fyre\DB\QueryLiteral;
use Fyre\DB\Types\BinaryType;
use Fyre\DB\Types\BooleanType;
use Fyre\DB\Types\DateTimeFractionalType;
use Fyre\DB\Types\DateTimeTimeZoneType;
use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\DateType;
use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\EnumType;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\JsonType;
use Fyre\DB\Types\SetType;
use Fyre\DB\Types\StringType;
use Fyre\DB\Types\TextType;
use Fyre\DB\Types\TimeType;
use InvalidArgumentException;

use function filter_var;
use function is_string;
use function sprintf;
use function str_starts_with;
use function strtolower;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;

/**
 * Defines a PostgreSQL column for DDL operations.
 */
class PostgresColumn extends Column
{
    /**
     * @var array<string, string>
     */
    protected static array $typeAliases = [
        'char' => 'character',
        'varchar' => 'character varying',
        'double' => 'double precision',
        'int' => 'integer',
        'time' => 'time without time zone',
        'timestamptz' => 'timestamp with time zone',
        'timestamp' => 'timestamp without time zone',
    ];

    /**
     * Constructs a PostgresColumn.
     *
     * @param PostgresTable $table The PostgresTable.
     * @param string $name The column name.
     * @param string $type The column type.
     * @param int|null $length The column length.
     * @param int|null $precision The column precision.
     * @param bool $nullable Whether the column is nullable.
     * @param bool|float|int|QueryLiteral|string|null $default The column default value.
     * @param string|null $comment The column comment.
     * @param bool $autoIncrement Whether the column is auto-incrementing.
     *
     * @throws InvalidArgumentException If the column type is not supported.
     */
    public function __construct(
        PostgresTable $table,
        string $name,
        string $type = StringType::class,
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

        switch ($this->type) {
            case BinaryType::class:
                $this->type = 'bytea';
                break;
            case BooleanType::class:
                $this->type = 'boolean';
                break;
            case DateTimeFractionalType::class:
                $this->type = 'timestamp without time zone';
                $this->fractionalSeconds ??= 6;
                break;
            case DateTimeTimeZoneType::class:
                $this->type = 'timestamp with time zone';
                $this->fractionalSeconds ??= 6;
                break;
            case DateTimeType::class:
                $this->type = 'timestamp without time zone';
                $this->fractionalSeconds ??= 0;
                break;
            case DateType::class:
                $this->type = 'date';
                break;
            case DecimalType::class:
                $this->type = 'numeric';
                break;
            case FloatType::class:
                $this->type = 'real';
                break;
            case IntegerType::class:
                $this->precision ??= 11;

                if ($this->precision <= 6) {
                    $this->type = 'smallint';
                } else if ($this->precision <= 11) {
                    $this->type = 'integer';
                } else {
                    $this->type = 'bigint';
                }
                break;
            case JsonType::class:
                $this->type = 'json';
                break;
            case StringType::class:
                $this->length ??= 80;

                $this->type = $this->length === 1 ?
                    'character' :
                    'character varying';
                break;
            case TextType::class:
                $this->type = 'text';
                break;
            case TimeType::class:
                $this->fractionalSeconds ??= 6;
                $this->type = 'time without time zone';
                break;
            case EnumType::class:
            case SetType::class:
                throw new InvalidArgumentException(sprintf(
                    'Column type `%s` is not supported by this connection.',
                    $this->type
                ));
            default:
                $this->type = strtolower($this->type);
                break;
        }

        $type = $this->type;

        $this->type = static::$typeAliases[$type] ?? $type;

        switch ($this->type) {
            case 'bpchar':
            case 'character':
                $this->length ??= 1;
                break;
            case 'character varying':
                $this->length ??= 80;
                break;
            default:
                $this->length = null;
                break;
        }

        switch ($this->type) {
            case 'smallint':
            case 'smallserial':
                $this->precision = 6;
                break;
            case 'integer':
            case 'serial':
                $this->precision = 11;
                break;
            case 'bigint':
            case 'bigserial':
                $this->precision = 20;
                break;
            case 'numeric':
                $this->precision ??= 10;
                break;
            default:
                $this->precision = null;
                break;
        }

        switch ($this->type) {
            case 'numeric':
                $this->scale ??= 0;
                break;
            default:
                $this->scale = null;
                break;
        }

        switch ($this->type) {
            case 'time without time zone':
            case 'timestamp without time zone':
            case 'timestamp with time zone':
                $this->fractionalSeconds ??= 6;
                break;
            default:
                $this->fractionalSeconds = null;
                break;
        }

        $this->default = static::parseDefaultValue($this->default, $this->type);
    }

    /**
     * Parses a column default value.
     *
     * Normalizes a default value for DDL generation and comparisons.
     *
     * - Leaves {@see QueryLiteral} defaults as-is.
     * - Normalizes `CURRENT_TIMESTAMP*` strings to {@see QueryLiteral}(`CURRENT_TIMESTAMP`).
     * - Casts numeric/boolean-looking scalars to their native PHP type when the column type implies it.
     *
     * @param bool|float|int|QueryLiteral|string|null $default The default value.
     * @param string $type The column type.
     * @return bool|float|int|QueryLiteral|string|null The normalized default.
     */
    protected static function parseDefaultValue(mixed $default, string $type): bool|float|int|QueryLiteral|string|null
    {
        if ($default === null || $default instanceof QueryLiteral) {
            return $default;
        }

        if (is_string($default) && str_starts_with(strtolower($default), 'current_timestamp')) {
            return new QueryLiteral('CURRENT_TIMESTAMP');
        }

        switch ($type) {
            case 'bigint':
            case 'integer':
            case 'smallint':
                return filter_var($default, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
            case 'boolean':
                return filter_var($default, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            case 'double precision':
            case 'numeric':
            case 'real':
                return filter_var($default, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
            default:
                return $default;
        }
    }
}
