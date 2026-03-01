<?php
declare(strict_types=1);

namespace Fyre\DB\Types;

use Fyre\DB\Type;
use Override;
use Stringable;

use function is_scalar;

/**
 * Represents a string value type.
 */
class StringType extends Type
{
    /**
     * {@inheritDoc}
     *
     * @return string|null The string value.
     */
    #[Override]
    public function parse(mixed $value): string|null
    {
        if ($value === null || (!is_scalar($value) && !($value instanceof Stringable))) {
            return null;
        }

        return (string) $value;
    }
}
