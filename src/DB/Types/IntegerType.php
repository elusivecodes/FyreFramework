<?php
declare(strict_types=1);

namespace Fyre\DB\Types;

use Fyre\DB\Type;
use Override;

use function filter_var;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_INT;

/**
 * Represents an integer value type.
 */
class IntegerType extends Type
{
    /**
     * {@inheritDoc}
     *
     * @return int|null The integer value.
     */
    #[Override]
    public function parse(mixed $value): int|null
    {
        if ($value === null) {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE);
    }
}
