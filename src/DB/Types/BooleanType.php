<?php
declare(strict_types=1);

namespace Fyre\DB\Types;

use Fyre\DB\Type;
use Override;

use function filter_var;

use const FILTER_NULL_ON_FAILURE;
use const FILTER_VALIDATE_BOOLEAN;

/**
 * Represents a boolean value type.
 */
class BooleanType extends Type
{
    /**
     * {@inheritDoc}
     *
     * @return bool|null The parsed boolean value.
     */
    #[Override]
    public function parse(mixed $value): bool|null
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
