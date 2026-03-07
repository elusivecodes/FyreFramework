<?php
declare(strict_types=1);

namespace Fyre\DB\Schema;

use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\QueryLiteral;
use Fyre\DB\Type;
use Fyre\DB\TypeParser;
use InvalidArgumentException;
use UnitEnum;

use function is_subclass_of;
use function sprintf;

/**
 * Represents schema column metadata.
 */
abstract class Column
{
    use DebugTrait;

    /**
     * @var array<string, string>
     */
    protected static array $types = [];

    /**
     * Constructs a Column.
     *
     * @param Table $table The Table.
     * @param TypeParser $typeParser The TypeParser.
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
     * @param class-string<UnitEnum>|null $enumClass The enum class.
     */
    public function __construct(
        protected Table $table,
        protected TypeParser $typeParser,
        protected string $name,
        protected string $type,
        protected int|null $length = null,
        protected int|null $precision = null,
        protected int|null $scale = null,
        protected int|null $fractionalSeconds = null,
        protected bool $nullable = false,
        protected bool $unsigned = false,
        protected bool|float|int|QueryLiteral|string|null $default = null,
        protected string|null $comment = null,
        protected bool $autoIncrement = false,
        protected string|null $enumClass = null,
    ) {}

    /**
     * Returns the default value for the column.
     *
     * Note: When the configured default is a {@see QueryLiteral}, this method will execute a `SELECT`
     * query to evaluate it.
     *
     * @return mixed The parsed default value.
     */
    public function defaultValue(): mixed
    {
        if ($this->default === null) {
            return $this->nullable ? null : '';
        }

        if ($this->default instanceof QueryLiteral) {
            return $this->table->getSchema()
                ->getConnection()
                ->rawQuery('SELECT '.(string) $this->default)
                ->fetchColumn();
        }

        return $this->type()->parse($this->default);
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
     * Returns the enum class.
     *
     * @return class-string<UnitEnum>|null The enum class.
     */
    public function getEnumClass(): string|null
    {
        return $this->enumClass;
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
     * Checks whether the column has an enum class.
     *
     * @return bool Whether the column has an enum class.
     */
    public function hasEnumClass(): bool
    {
        return $this->enumClass !== null;
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
     * Sets the enum class.
     *
     * @param class-string<UnitEnum>|null $enumClass The enum class.
     * @return static The Column instance.
     */
    public function setEnumClass(string|null $enumClass): static
    {
        if ($enumClass !== null && !is_subclass_of($enumClass, UnitEnum::class, true)) {
            throw new InvalidArgumentException(sprintf(
                'Enum class `%s` must implement `%s`.',
                $enumClass,
                UnitEnum::class
            ));
        }

        $this->enumClass = $enumClass;

        return $this;
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
            'enumClass' => $this->enumClass,
        ];
    }

    /**
     * Returns the resolved Type for the column.
     *
     * @return Type The Type instance.
     */
    public function type(): Type
    {
        return (static::$types[$this->type] ?? 'string') |> $this->typeParser->use(...);
    }
}
