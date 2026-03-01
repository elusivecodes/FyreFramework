<?php
declare(strict_types=1);

namespace Fyre\DB\Forge;

use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\QueryLiteral;
use Fyre\DB\Schema\Column as SchemaColumn;
use Fyre\DB\Types\StringType;

use function strtolower;

/**
 * Defines a table column for DDL operations.
 */
abstract class Column
{
    use DebugTrait;

    /**
     * Checks whether two default values are equivalent.
     *
     * This method is intended for schema/DDL diffing, where defaults are represented as either scalars
     * (string|int|float|bool|null) or a {@see QueryLiteral} for raw SQL expressions.
     *
     * - Scalars are compared strictly (no type juggling): e.g. `"1"` is not equal to `1`.
     * - {@see QueryLiteral} instances are compared by their string value (case-insensitive only). No SQL parsing or
     *   canonicalization is performed (e.g. `CURRENT_TIMESTAMP` and `CURRENT_TIMESTAMP()` are not considered equal).
     *
     * @param bool|float|int|QueryLiteral|string|null $a The first default value.
     * @param bool|float|int|QueryLiteral|string|null $b The second default value.
     * @return bool Whether the defaults are equivalent.
     */
    public static function compareDefaultValues(mixed $a, mixed $b): bool
    {
        if ($a instanceof QueryLiteral && $b instanceof QueryLiteral) {
            return strtolower((string) $a) === strtolower((string) $b);
        }

        return $a === $b;
    }

    /**
     * Constructs a Column.
     *
     * @param Table $table The Table.
     * @param string $name The column name.
     * @param string $type The column type.
     * @param int|null $length The column length.
     * @param int|null $precision The column precision.
     * @param int|null $scale The column scale.
     * @param int|null $fractionalSeconds The fractional seconds precision.
     * @param bool $nullable Whether the column is nullable.
     * @param bool $unsigned Whether the column is unsigned.
     * @param bool|float|int|QueryLiteral|string|null $default The column default value.
     * @param string|null $comment The column comment.
     * @param bool $autoIncrement Whether the column is auto-incrementing.
     */
    public function __construct(
        protected Table $table,
        protected string $name,
        protected string $type = StringType::class,
        protected int|null $length = null,
        protected int|null $precision = null,
        protected int|null $scale = null,
        protected int|null $fractionalSeconds = null,
        protected bool $nullable = false,
        protected bool $unsigned = false,
        protected bool|float|int|QueryLiteral|string|null $default = null,
        protected string|null $comment = null,
        protected bool $autoIncrement = false,
    ) {}

    /**
     * Checks whether this column is equivalent to a SchemaColumn.
     *
     * @param SchemaColumn $schemaColumn The SchemaColumn.
     * @return bool Whether the columns are equivalent.
     */
    public function compare(SchemaColumn $schemaColumn): bool
    {
        return $this->type === $schemaColumn->getType() &&
            $this->length === $schemaColumn->getLength() &&
            $this->precision === $schemaColumn->getPrecision() &&
            $this->scale === $schemaColumn->getScale() &&
            $this->fractionalSeconds === $schemaColumn->getFractionalSeconds() &&
            $this->nullable === $schemaColumn->isNullable() &&
            $this->unsigned === $schemaColumn->isUnsigned() &&
            $this->compareDefaultValues($this->default, $schemaColumn->getDefault()) &&
            $this->comment === $schemaColumn->getComment() &&
            $this->autoIncrement === $schemaColumn->isAutoIncrement();
    }

    /**
     * Returns the column comment.
     *
     * @return string|null The column comment.
     */
    public function getComment(): string|null
    {
        return $this->comment;
    }

    /**
     * Returns the column default value.
     *
     * @return bool|float|int|QueryLiteral|string|null The column default value.
     */
    public function getDefault(): bool|float|int|QueryLiteral|string|null
    {
        return $this->default;
    }

    /**
     * Returns the fractional seconds precision.
     *
     * @return int|null The fractional seconds precision.
     */
    public function getFractionalSeconds(): int|null
    {
        return $this->fractionalSeconds;
    }

    /**
     * Returns the column length.
     *
     * @return int|null The column length.
     */
    public function getLength(): int|null
    {
        return $this->length;
    }

    /**
     * Returns the column name.
     *
     * @return string The column name.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the column precision.
     *
     * @return int|null The column precision.
     */
    public function getPrecision(): int|null
    {
        return $this->precision;
    }

    /**
     * Returns the column scale.
     *
     * @return int|null The column scale.
     */
    public function getScale(): int|null
    {
        return $this->scale;
    }

    /**
     * Returns the Table.
     *
     * @return Table The Table instance.
     */
    public function getTable(): Table
    {
        return $this->table;
    }

    /**
     * Returns the column type.
     *
     * @return string The column type.
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Checks whether the column is an auto increment column.
     *
     * @return bool Whether the column is an auto increment column.
     */
    public function isAutoIncrement(): bool
    {
        return $this->autoIncrement;
    }

    /**
     * Checks whether the column is nullable.
     *
     * @return bool Whether the column is nullable.
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * Checks whether the column is unsigned.
     *
     * @return bool Whether the column is unsigned.
     */
    public function isUnsigned(): bool
    {
        return $this->unsigned;
    }

    /**
     * Returns the column data as an array.
     *
     * @return array<string, mixed> The column data.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'length' => $this->length,
            'precision' => $this->precision,
            'scale' => $this->scale,
            'fractionalSeconds' => $this->fractionalSeconds,
            'nullable' => $this->nullable,
            'unsigned' => $this->unsigned,
            'default' => $this->default,
            'comment' => $this->comment,
            'autoIncrement' => $this->autoIncrement,
        ];
    }
}
