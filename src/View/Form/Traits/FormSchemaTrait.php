<?php
declare(strict_types=1);

namespace Fyre\View\Form\Traits;

use Fyre\DB\Types\BinaryType;
use Fyre\DB\Types\BooleanType;
use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\DateType;
use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;
use Fyre\DB\Types\TextType;
use Fyre\DB\Types\TimeType;
use Fyre\Form\Schema;

use function pow;

/**
 * Adds schema-driven helpers to form contexts.
 *
 * These helpers derive HTML-oriented field metadata (type, min/max) from the form schema.
 */
trait FormSchemaTrait
{
    /**
     * Returns the default value.
     *
     * @param Schema $schema The Schema.
     * @param string $field The field name.
     * @return mixed The parsed default value.
     */
    public static function getSchemaDefaultValue(Schema $schema, string $field): mixed
    {
        if (!$schema->hasField($field)) {
            return null;
        }

        $formField = $schema->field($field);

        return $formField->getDefault() |> $formField->type()->parse(...);
    }

    /**
     * Returns the maximum value.
     *
     * @param Schema $schema The Schema.
     * @param string $field The field name.
     * @return float|null The maximum value.
     */
    public static function getSchemaMax(Schema $schema, string $field): float|null
    {
        if (!$schema->hasField($field)) {
            return null;
        }

        $formField = $schema->field($field);

        if ($formField->type() instanceof FloatType) {
            return null;
        }

        $precision = $formField->getPrecision();
        $scale = $formField->getScale() ?? 0;

        if (!$precision) {
            return null;
        }

        $precisionMax = $precision > $scale ?
            pow(10, $precision - $scale) :
            1;
        $scaleMin = $scale ?
            (1 / pow(10, $scale)) :
            1;
        $max = $precisionMax - $scaleMin;

        if ($max > PHP_INT_MAX) {
            return null;
        }

        return $max;
    }

    /**
     * Returns the maximum length.
     *
     * @param Schema $schema The Schema.
     * @param string $field The field name.
     * @return int|null The maximum length.
     */
    public static function getSchemaMaxLength(Schema $schema, string $field): int|null
    {
        if (!$schema->hasField($field)) {
            return null;
        }

        $formField = $schema->field($field);
        $length = $formField->getLength();

        if ($formField->type() instanceof StringType && $length < 524288) {
            return $length;
        }

        return null;
    }

    /**
     * Returns the minimum value.
     *
     * @param Schema $schema The Schema.
     * @param string $field The field name.
     * @return float|null The minimum value.
     */
    public static function getSchemaMin(Schema $schema, string $field): float|null
    {
        if (!$schema->hasField($field)) {
            return null;
        }

        $formField = $schema->field($field);

        $precision = $formField->getPrecision();
        $scale = $formField->getScale() ?? 0;

        if (!$precision) {
            return null;
        }

        $precisionMax = $precision > $scale ?
            pow(10, $precision - $scale) :
            1;
        $scaleMin = $scale ?
            (1 / pow(10, $scale)) :
            1;
        $min = -($precisionMax - $scaleMin);

        if ($min < PHP_INT_MIN) {
            return null;
        }

        return $min;
    }

    /**
     * Returns the step interval.
     *
     * @param Schema $schema The Schema.
     * @param string $field The field name.
     * @return float|string|null The step interval.
     */
    public static function getSchemaStep(Schema $schema, string $field): float|string|null
    {
        if (!$schema->hasField($field)) {
            return null;
        }

        $formField = $schema->field($field);
        $type = $formField->type();

        if ($type instanceof IntegerType) {
            return 1;
        }

        if ($type instanceof FloatType) {
            return 'any';
        }

        if ($type instanceof DecimalType) {
            $scale = $formField->getScale() ?? 0;

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
     * @param Schema $schema The Schema.
     * @param string $field The field name.
     * @return string The field type.
     */
    public static function getSchemaType(Schema $schema, string $field): string
    {
        if (!$schema->hasField($field)) {
            return 'text';
        }

        $type = $schema->field($field)->type();

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

        if ($type instanceof BinaryType) {
            return 'file';
        }

        return 'text';
    }
}
