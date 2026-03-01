<?php
declare(strict_types=1);

namespace Fyre\DB\Types;

use Fyre\DB\Type;
use Override;

use function is_numeric;

/**
 * Represents a decimal/numeric value type.
 */
class DecimalType extends Type
{
    /**
     * {@inheritDoc}
     *
     * @return string|null The decimal string value.
     */
    #[Override]
    public function parse(mixed $value): string|null
    {
        if ($value === null || !is_numeric($value)) {
            return null;
        }

        return (string) $value;
    }
}
