<?php
declare(strict_types=1);

namespace Fyre\DB;

use Fyre\Core\Traits\DebugTrait;

/**
 * Provides a base database type converter.
 *
 * Type implementations convert values between database representations and PHP values.
 */
abstract class Type
{
    use DebugTrait;

    /**
     * Parses a database value to PHP value.
     *
     * @param mixed $value The database value.
     * @return mixed The PHP value.
     */
    public function fromDatabase(mixed $value): mixed
    {
        return $this->parse($value);
    }

    /**
     * Parses a user value to PHP value.
     *
     * @param mixed $value The user value.
     * @return mixed The PHP value.
     */
    public function parse(mixed $value): mixed
    {
        return $value;
    }

    /**
     * Parses a PHP value to database value.
     *
     * @param mixed $value The PHP value.
     * @return mixed The database value.
     */
    public function toDatabase(mixed $value): mixed
    {
        return $this->parse($value);
    }
}
