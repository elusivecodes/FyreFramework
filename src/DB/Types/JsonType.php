<?php
declare(strict_types=1);

namespace Fyre\DB\Types;

use Fyre\DB\Type;
use Override;

use function json_decode;
use function json_encode;

/**
 * Represents a JSON value type.
 *
 * Converts between JSON strings and PHP arrays.
 */
class JsonType extends Type
{
    protected int $encodingFlags = 0;

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function fromDatabase(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return json_decode($value, true);
    }

    /**
     * Sets the encoding flags.
     *
     * @param int $flags The encoding flags.
     * @return static The JsonType instance.
     */
    public function setEncodingFlags(int $flags): static
    {
        $this->encodingFlags = $flags;

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @return string|null The database value.
     */
    #[Override]
    public function toDatabase(mixed $value): string|null
    {
        if ($value === null) {
            return null;
        }

        return (string) json_encode($value, $this->encodingFlags);
    }
}
