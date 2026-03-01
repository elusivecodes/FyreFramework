<?php
declare(strict_types=1);

namespace Fyre\View\Form\Traits;

use Fyre\DB\Schema\Handlers\Mysql\MysqlColumn;
use Fyre\DB\Schema\Table;
use Fyre\DB\Types\BinaryType;
use Fyre\DB\Types\BooleanType;
use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\DateType;
use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\EnumType;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\SetType;
use Fyre\DB\Types\StringType;
use Fyre\DB\Types\TextType;
use Fyre\DB\Types\TimeType;

use function array_combine;
use function assert;
use function max;
use function min;
use function pow;

/**
 * Adds schema-driven helpers to form contexts.
 *
 * These helpers derive HTML-oriented field metadata (type, min/max, options) from the
 * database schema.
 */
trait DbSchemaTrait
{
    protected const MAX_VALUES = [
        'tinyint' => 127,
        'smallint' => 32767,
        'mediumint' => 8388607,
        'int' => 2147483647,
        'integer' => 2147483647,
    ];

    /**
     * Returns the default value.
     *
     * @param Table $schema The Table.
     * @param string $field The field name.
     * @return mixed The default value.
     */
    public static function getSchemaDefaultValue(Table $schema, string $field): mixed
    {
        if (!$schema->hasColumn($field)) {
            return null;
        }

        $column = $schema->column($field);

        return $column->defaultValue() |> $column->type()->parse(...);
    }

    /**
     * Returns the maximum value.
     *
     * @param Table $schema The Table.
     * @param string $field The field name.
     * @return float|null The maximum value.
     */
    public static function getSchemaMax(Table $schema, string $field): float|null
    {
        if (!$schema->hasColumn($field)) {
            return null;
        }

        $column = $schema->column($field);

        if ($column->type() instanceof FloatType) {
            return null;
        }

        $type = $column->getType();
        $unsigned = $column->isUnsigned();

        $max = static::MAX_VALUES[$type] ?? null;

        if ($unsigned && $max) {
            $max = ($max * 2) + 1;
        }

        $precision = $column->getPrecision();
        $scale = $column->getScale() ?? 0;

        if (!$precision) {
            return $max;
        }

        $precisionMax = $precision > $scale ?
            pow(10, $precision - $scale) :
            1;
        $scaleMin = $scale ?
            (1 / pow(10, $scale)) :
            1;
        $schemaMax = $precisionMax - $scaleMin;

        $max = $max ? min($max, $schemaMax) : $schemaMax;

        if ($max > PHP_INT_MAX) {
            return null;
        }

        return $max;
    }

    /**
     * Returns the maximum length.
     *
     * @param Table $schema The Table.
     * @param string $field The field name.
     * @return int|null The maximum length.
     */
    public static function getSchemaMaxLength(Table $schema, string $field): int|null
    {
        if (!$schema->hasColumn($field)) {
            return null;
        }

        $column = $schema->column($field);
        $length = $column->getLength();

        if ($column->type() instanceof StringType && $length < 524288) {
            return $length;
        }

        return null;
    }

    /**
     * Returns the minimum value.
     *
     * @param Table $schema The Table.
     * @param string $field The field name.
     * @return float|null The minimum value.
     */
    public static function getSchemaMin(Table $schema, string $field): float|null
    {
        if (!$schema->hasColumn($field)) {
            return null;
        }

        $column = $schema->column($field);

        if ($column->isUnsigned()) {
            return 0;
        }

        if ($column->type() instanceof FloatType) {
            return null;
        }

        $type = $column->getType();
        $precision = $column->getPrecision();
        $scale = $column->getScale() ?? 0;

        if (isset(static::MAX_VALUES[$type])) {
            $min = (static::MAX_VALUES[$type] + 1) * -1;
        } else {
            $min = null;
        }

        if (!$precision) {
            return $min;
        }

        $precisionMax = $precision > $scale ?
            pow(10, $precision - $scale) :
            1;
        $scaleMin = $scale ?
            (1 / pow(10, $scale)) :
            1;
        $schemaMax = $precisionMax - $scaleMin;

        $min = $min ? max($min, -$schemaMax) : -$schemaMax;

        if ($min < PHP_INT_MIN) {
            return null;
        }

        return $min;
    }

    /**
     * Returns the option values.
     *
     * @param Table $schema The Table.
     * @param string $field The field name.
     * @return array<string, string>|null The option values.
     */
    public static function getSchemaOptionValues(Table $schema, string $field): array|null
    {
        if (!$schema->hasColumn($field)) {
            return null;
        }

        $column = $schema->column($field);
        $type = $column->type();

        if ($type instanceof EnumType || $type instanceof SetType) {
            assert($column instanceof MysqlColumn);

            $values = $column->getValues() ?? [];

            return array_combine($values, $values);
        }

        return null;
    }

    /**
     * Returns the step interval.
     *
     * @param Table $schema The Table.
     * @param string $field The field name.
     * @return float|null The step interval.
     */
    public static function getSchemaStep(Table $schema, string $field): float|string|null
    {
        if (!$schema->hasColumn($field)) {
            return null;
        }

        $column = $schema->column($field);
        $type = $column->type();

        if ($type instanceof IntegerType) {
            return 1;
        }

        if ($type instanceof FloatType) {
            return 'any';
        }

        if ($type instanceof DecimalType) {
            $scale = $column->getScale() ?? 0;

            if ($scale > 0) {
                return 1 / pow(10, $scale);
            }

            if ($scale === 0) {
                return 1;
            }
        }

        return null;
    }

    /**
     * Returns the field type.
     *
     * @param Table $schema The Table.
     * @param string $field The field name.
     * @return string The field type.
     */
    public static function getSchemaType(Table $schema, string $field): string
    {
        if (!$schema->hasColumn($field)) {
            return 'text';
        }

        $type = $schema->column($field)->type();

        if ($type instanceof BooleanType) {
            return 'checkbox';
        }

        if ($type instanceof DateType) {
            return 'date';
        }

        if ($type instanceof TimeType) {
            return 'time';
        }

        if ($type instanceof DateTimeType) {
            return 'datetime';
        }

        if ($type instanceof DecimalType || $type instanceof FloatType || $type instanceof IntegerType) {
            return 'number';
        }

        if ($type instanceof TextType) {
            return 'textarea';
        }

        if ($type instanceof EnumType) {
            return 'select';
        }

        if ($type instanceof SetType) {
            return 'selectMulti';
        }

        if ($type instanceof BinaryType) {
            return 'file';
        }

        return 'text';
    }
}
