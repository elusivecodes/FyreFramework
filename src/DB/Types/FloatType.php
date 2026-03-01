<?php
declare(strict_types=1);

namespace Fyre\DB\Types;

use Fyre\DB\Type;
use Override;

use function filter_var;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_FLOAT;

/**
 * Represents a floating point value type.
 */
class FloatType extends Type
{
    /**
     * {@inheritDoc}
     *
     * @return float|null The floating point value.
     */
    #[Override]
    public function parse(mixed $value): float|null
    {
        if ($value === null) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE);
    }
}
