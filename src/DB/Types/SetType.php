<?php
declare(strict_types=1);

namespace Fyre\DB\Types;

use Fyre\DB\Type;
use Override;
use Stringable;

use function explode;
use function implode;
use function is_array;
use function is_string;

/**
 * Represents a SET value type.
 */
class SetType extends Type
{
    /**
     * {@inheritDoc}
     *
     * @return mixed[]|null The set values.
     */
    #[Override]
    public function parse(mixed $value): array|null
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value) || $value instanceof Stringable) {
            return explode(',', (string) $value);
        }

        return null;
    }

    /**
     * {@inheritDoc}
     *
     * @return string|null The database value.
     */
    #[Override]
    public function toDatabase(mixed $value): string|null
    {
        if (is_array($value)) {
            return implode(',', $value);
        }

        if (is_string($value) || $value instanceof Stringable) {
            return (string) $value;
        }

        return null;
    }
}
