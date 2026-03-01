<?php
declare(strict_types=1);

namespace Fyre\View\Form;

use Fyre\Core\Traits\DebugTrait;

/**
 * Provides the base form context API.
 *
 * Concrete contexts supply values and field metadata (e.g. options, constraints) used by
 * form helpers; default implementations return null.
 */
abstract class Context
{
    use DebugTrait;

    /**
     * Returns the default value of a field.
     *
     * @param string $key The field key.
     * @return mixed The default value.
     */
    public function getDefaultValue(string $key): mixed
    {
        return null;
    }

    /**
     * Returns the maximum value.
     *
     * @param string $key The field key.
     * @return float|null The maximum value.
     */
    public function getMax(string $key): float|null
    {
        return null;
    }

    /**
     * Returns the maximum length.
     *
     * @param string $key The field key.
     * @return int|null The maximum length.
     */
    public function getMaxLength(string $key): int|null
    {
        return null;
    }

    /**
     * Returns the minimum value.
     *
     * @param string $key The field key.
     * @return float|null The minimum value.
     */
    public function getMin(string $key): float|null
    {
        return null;
    }

    /**
     * Returns the option values for a field.
     *
     * @param string $key The field key.
     * @return array<string, mixed>|null The options.
     */
    abstract public function getOptionValues(string $key): array|null;

    /**
     * Returns the step interval.
     *
     * @param string $key The field key.
     * @return float|string|null The step interval.
     */
    public function getStep(string $key): float|string|null
    {
        return null;
    }

    /**
     * Returns the field type.
     *
     * @param string $key The field key.
     * @return string The field type.
     */
    public function getType(string $key): string
    {
        return 'text';
    }

    /**
     * Returns the value of a field.
     *
     * @param string $key The field key.
     * @return mixed The value.
     */
    abstract public function getValue(string $key): mixed;

    /**
     * Checks whether the field is required.
     *
     * @param string $key The field key.
     * @return bool Whether the field is required.
     */
    public function isRequired(string $key): bool
    {
        return false;
    }
}
