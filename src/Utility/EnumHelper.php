<?php
declare(strict_types=1);

namespace Fyre\Utility;

use BackedEnum;
use UnitEnum;

use function is_subclass_of;

/**
 * Provides helpers for working with PHP enums.
 */
final class EnumHelper
{
    /**
     * Converts an enum case to its normalized scalar value.
     *
     * @param mixed $value The value.
     * @return mixed The normalized value.
     */
    public static function normalizeValue(mixed $value): mixed
    {
        if (!($value instanceof UnitEnum)) {
            return $value;
        }

        return $value instanceof BackedEnum ?
            $value->value :
            $value->name;
    }

    /**
     * Converts a scalar value to an enum case.
     *
     * @param class-string<UnitEnum> $enumClass The enum class.
     * @param mixed $value The scalar value.
     * @return mixed The converted value.
     */
    public static function parseValue(string $enumClass, mixed $value): mixed
    {
        if ($value === null || $value instanceof $enumClass) {
            return $value;
        }

        if (is_subclass_of($enumClass, BackedEnum::class, true)) {
            /** @var class-string<BackedEnum> $enumClass */
            return $enumClass::tryFrom($value);
        }

        foreach ($enumClass::cases() as $case) {
            if ($case->name === $value) {
                return $case;
            }
        }

        return null;
    }
}
