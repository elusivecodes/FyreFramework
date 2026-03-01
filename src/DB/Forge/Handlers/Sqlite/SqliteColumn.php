<?php
declare(strict_types=1);

namespace Fyre\DB\Forge\Handlers\Sqlite;

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
 * Defines a SQLite column for DDL operations.
 */
class SqliteColumn extends Column
{
    /**
     * Constructs a SqliteColumn.
     *
     * @param SqliteTable $table The SqliteTable.
     * @param string $name The column name.
     * @param string $type The column type.
     * @param int|null $length The column length.
     * @param int|null $precision The column precision.
     * @param bool $nullable Whether the column is nullable.
     * @param bool $unsigned Whether the column is unsigned.
     * @param bool|float|int|QueryLiteral|string|null $default The column default value.
     * @param bool $autoIncrement Whether the column is auto-incrementing.
     *
     * @throws InvalidArgumentException If the column type is not supported.
     */
    public function __construct(
        SqliteTable $table,
        string $name,
        string $type = StringType::class,
        int|null $length = null,
        int|null $precision = null,
        int|null $scale = null,
        int|null $fractionalSeconds = null,
        bool $nullable = false,
        bool $unsigned = false,
        bool|float|int|QueryLiteral|string|null $default = null,
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
            $unsigned,
            $default,
            null,
            $autoIncrement
        );

        switch ($this->type) {
            case BinaryType::class:
                $this->type = 'blob';
                break;
            case BooleanType::class:
                $this->type = 'boolean';
                break;
            case DateTimeFractionalType::class:
                $this->type = 'datetimefractional';
                $this->fractionalSeconds ??= 6;
                break;
            case DateTimeTimeZoneType::class:
                $this->type = 'timestamptimezone';
                $this->fractionalSeconds ??= 6;
                break;
            case DateTimeType::class:
                $this->type = 'datetime';
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

                if ($this->precision <= 4) {
                    $this->type = 'tinyint';
                } else if ($this->precision <= 6) {
                    $this->type = 'smallint';
                } else if ($this->precision <= 8) {
                    $this->type = 'mediumint';
                } else if ($this->precision <= 9) {
                    $this->type = 'int';
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
                    'char' :
                    'varchar';
                break;
            case TextType::class:
                $this->type = 'text';
                break;
            case TimeType::class:
                $this->type = 'time';
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

        switch ($this->type) {
            case 'char':
                $this->length ??= 1;
                break;
            case 'varchar':
                $this->length ??= 80;
                break;
            default:
                $this->length = null;
                break;
        }

        switch ($this->type) {
            case 'tinyint':
                $this->precision ??= 4;
                break;
            case 'smallint':
                $this->precision ??= 6;
                break;
            case 'mediumint':
                $this->precision ??= 8;
                break;
            case 'int':
                $this->precision ??= 11;
                break;
            case 'bigint':
                $this->precision ??= 20;
                break;
            case 'decimal':
            case 'numeric':
                $this->precision ??= 10;
                break;
            default:
                $this->precision = null;
                break;
        }

        switch ($this->type) {
            case 'decimal':
            case 'numeric':
                $this->scale ??= 0;
                break;
            default:
                $this->scale = null;
                break;
        }

        switch ($this->type) {
            case 'datetime':
            case 'datetimefractional':
            case 'time':
            case 'timestamp':
            case 'timestamptimezone':
                if (!$this->fractionalSeconds) {
                    $this->fractionalSeconds = null;
                }
                break;
            default:
                $this->fractionalSeconds = null;
                break;
        }

        switch ($this->type) {
            case 'decimal':
            case 'numeric':
            case 'bit':
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'integer':
            case 'bigint':
            case 'float':
            case 'real':
            case 'double':
                break;
            default:
                $this->unsigned = false;
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

        if ($type === 'boolean') {
            return filter_var($default, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        if (is_string($default) && str_starts_with(strtolower($default), 'current_timestamp')) {
            return new QueryLiteral('CURRENT_TIMESTAMP');
        }

        return filter_var($default, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ??
            filter_var($default, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) ??
            $default;
    }
}
