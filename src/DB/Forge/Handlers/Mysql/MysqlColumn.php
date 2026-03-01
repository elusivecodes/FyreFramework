<?php
declare(strict_types=1);

namespace Fyre\DB\Forge\Handlers\Mysql;

use Fyre\DB\Forge\Column;
use Fyre\DB\QueryLiteral;
use Fyre\DB\Schema\Column as SchemaColumn;
use Fyre\DB\Schema\Handlers\Mysql\MysqlColumn as MysqlSchemaColumn;
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
use Override;

use function assert;
use function filter_var;
use function is_string;
use function str_contains;
use function str_starts_with;
use function strtolower;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_FLOAT;
use const FILTER_VALIDATE_INT;

/**
 * Defines a MySQL column for DDL operations.
 */
class MysqlColumn extends Column
{
    /**
     * Constructs a MysqlColumn.
     *
     * @param MysqlTable $table The MysqlTable.
     * @param string $name The column name.
     * @param string $type The column type.
     * @param int|null $length The column length.
     * @param int|null $precision The column precision.
     * @param bool $nullable Whether the column is nullable.
     * @param bool $unsigned Whether the column is unsigned.
     * @param bool|float|int|QueryLiteral|string|null $default The column default value.
     * @param string|null $comment The column comment.
     * @param bool $autoIncrement Whether the column is auto-incrementing.
     * @param string[]|null $values The column values.
     * @param string|null $charset The column character set.
     * @param string|null $collation The column collation.
     */
    public function __construct(
        MysqlTable $table,
        string $name,
        string $type = StringType::class,
        int|null $length = null,
        int|null $precision = null,
        int|null $scale = null,
        int|null $fractionalSeconds = null,
        bool $nullable = false,
        bool $unsigned = false,
        bool|float|int|QueryLiteral|string|null $default = null,
        string|null $comment = '',
        bool $autoIncrement = false,
        protected array|null $values = null,
        protected string|null $charset = null,
        protected string|null $collation = null,
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
            $comment,
            $autoIncrement
        );

        assert($this->table instanceof MysqlTable);

        switch ($this->type) {
            case BinaryType::class:
                $this->length ??= 65535;

                if ($this->length <= 255) {
                    $this->type = 'tinyblob';
                } else if ($this->length <= 65535) {
                    $this->type = 'blob';
                } else if ($this->length <= 16777215) {
                    $this->type = 'mediumblob';
                } else {
                    $this->type = 'longblob';
                }
                break;
            case BooleanType::class:
                $this->type = 'tinyint';
                $this->precision = 1;
                break;
            case DateTimeFractionalType::class:
                $this->type = 'datetime';
                $this->fractionalSeconds ??= 6;
                break;
            case DateTimeTimeZoneType::class:
            case DateTimeType::class:
                $this->type = 'datetime';
                break;
            case DateType::class:
                $this->type = 'date';
                break;
            case DecimalType::class:
                $this->type = 'decimal';
                break;
            case EnumType::class:
                $this->type = 'enum';
                break;
            case FloatType::class:
                $this->type = 'float';
                break;
            case IntegerType::class:
                $this->unsigned ??= false;
                $this->precision ??= 11;

                if ($this->precision <= 4) {
                    $this->type = 'tinyint';
                } else if ($this->precision <= 6) {
                    $this->type = 'smallint';
                } else if ($this->precision <= 8) {
                    $this->type = 'mediumint';
                } else if ($this->precision <= 11) {
                    $this->type = 'int';
                } else {
                    $this->type = 'bigint';
                }
                break;
            case JsonType::class:
                $this->type = 'json';
                break;
            case SetType::class:
                $this->type = 'set';
                break;
            case StringType::class:
                $this->length ??= 80;

                $this->type = $this->length === 1 ?
                    'char' :
                    'varchar';
                break;
            case TextType::class:
                $this->length ??= 65535;

                if ($this->length <= 255) {
                    $this->type = 'tinytext';
                } else if ($this->length <= 65535) {
                    $this->type = 'text';
                } else if ($this->length <= 16777215) {
                    $this->type = 'mediumtext';
                } else {
                    $this->type = 'longtext';
                }
                break;
            case TimeType::class:
                $this->type = 'time';
                break;
            default:
                $this->type = strtolower($this->type);
                break;
        }

        if ($this->type === 'json' && str_contains($this->table->getForge()->getConnection()->version(), 'MariaDB')) {
            $this->type = 'longtext';
            $this->charset = 'utf8mb4';
            $this->collation = 'utf8mb4_bin';
        }

        switch ($this->type) {
            case 'char':
            case 'varchar':
            case 'tinytext':
            case 'text':
            case 'mediumtext':
            case 'longtext':
            case 'enum':
            case 'set':
                $this->charset ??= $this->table->getCharset();
                $this->collation ??= $this->table->getCollation();
                break;
            default:
                $this->charset = null;
                $this->collation = null;
                break;
        }

        switch ($this->type) {
            case 'char':
                $this->length ??= 1;
                break;
            case 'varchar':
                $this->length ??= 80;
                break;
            case 'tinyblob':
            case 'tinytext':
                $this->length = 255;
                break;
            case 'blob':
            case 'text':
                $this->length = 65535;
                break;
            case 'mediumblob':
            case 'mediumtext':
                $this->length = 16777215;
                break;
            case 'longblob':
            case 'longtext':
                $this->length = 4294967295;
                break;
            case 'binary':
            case 'varbinary':
                break;
            default:
                $this->length = null;
                break;
        }

        switch ($this->type) {
            case 'bit':
                $this->precision ??= 1;
                break;
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
                $this->precision ??= 10;
                break;
            default:
                $this->precision = null;
                break;
        }

        switch ($this->type) {
            case 'decimal':
                $this->scale ??= 0;
                break;
            default:
                $this->scale = null;
                break;
        }

        switch ($this->type) {
            case 'datetime':
            case 'time':
                if (!$this->fractionalSeconds) {
                    $this->fractionalSeconds = null;
                }
                break;
            default:
                $this->fractionalSeconds = null;
                break;
        }

        switch ($this->type) {
            case 'enum':
            case 'set':
                $this->values ??= [];
                break;
            default:
                $this->values = null;
                break;
        }

        switch ($this->type) {
            case 'decimal':
            case 'bit':
            case 'tinyint':
            case 'smallint':
            case 'mediumint':
            case 'int':
            case 'bigint':
            case 'float':
            case 'double':
                break;
            default:
                $this->unsigned = false;
                break;
        }

        $this->default = static::parseDefaultValue($this->default, $this->type, $this->precision);

        if ($this->type === 'timestamp') {
            $this->default ??= new QueryLiteral('CURRENT_TIMESTAMP');
        }
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function compare(SchemaColumn $schemaColumn): bool
    {
        assert($schemaColumn instanceof MysqlSchemaColumn);

        return parent::compare($schemaColumn) &&
            $this->values === $schemaColumn->getValues() &&
            $this->charset === $schemaColumn->getCharset() &&
            $this->collation === $schemaColumn->getCollation();
    }

    /**
     * Returns the column character set.
     *
     * @return string|null The column character set.
     */
    public function getCharset(): string|null
    {
        return $this->charset;
    }

    /**
     * Returns the column collation.
     *
     * @return string|null The column collation.
     */
    public function getCollation(): string|null
    {
        return $this->collation;
    }

    /**
     * Returns the column enum values.
     *
     * @return string[]|null The column enum values.
     */
    public function getValues(): array|null
    {
        return $this->values;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'length' => $this->length,
            'precision' => $this->precision,
            'scale' => $this->scale,
            'fractionalSeconds' => $this->fractionalSeconds,
            'values' => $this->values,
            'nullable' => $this->nullable,
            'unsigned' => $this->unsigned,
            'default' => $this->default,
            'charset' => $this->charset,
            'collation' => $this->collation,
            'comment' => $this->comment,
            'autoIncrement' => $this->autoIncrement,
        ];
    }

    /**
     * Parses a column default value.
     *
     * Normalizes a default value for DDL generation and comparisons.
     *
     * - Leaves {@see QueryLiteral} defaults as-is.
     * - Normalizes `CURRENT_TIMESTAMP*` strings to {@see QueryLiteral}(`CURRENT_TIMESTAMP`).
     * - Casts numeric/boolean-looking scalars to their native PHP type when the column type implies it.
     * - Treats `tinyint(1)` as boolean.
     *
     * @param bool|float|int|QueryLiteral|string|null $default The default value.
     * @param string $type The column type.
     * @param int|null $precision The numeric precision/display width.
     * @return bool|float|int|QueryLiteral|string|null The normalized default.
     */
    protected static function parseDefaultValue(mixed $default, string $type, int|null $precision): bool|float|int|QueryLiteral|string|null
    {
        if ($default === null || $default instanceof QueryLiteral) {
            return $default;
        }

        if (is_string($default) && str_starts_with(strtolower($default), 'current_timestamp')) {
            return new QueryLiteral('CURRENT_TIMESTAMP');
        }

        if ($type === 'tinyint' && $precision === 1) {
            $type = 'boolean';
        }

        switch ($type) {
            case 'bigint':
            case 'int':
            case 'integer':
            case 'mediumint':
            case 'smallint':
            case 'tinyint':
                return filter_var($default, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
            case 'boolean':
                return filter_var($default, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            case 'decimal':
            case 'double':
            case 'float':
            case 'numeric':
            case 'real':
                return filter_var($default, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
            default:
                return $default;
        }
    }
}
