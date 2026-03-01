<?php
declare(strict_types=1);

namespace Fyre\Core\Exceptions;

use function error_get_last;
use function is_array;

/**
 * Represents a PHP error as an exception.
 */
final class ErrorException extends \ErrorException
{
    /**
     * Creates an ErrorException from the last error.
     *
     * @param string|null $file The file to match.
     * @param int|null $line The line to match.
     * @return self|null The new ErrorException instance, or null if no matching error is available.
     */
    public static function forLastError(string|null $file = null, int|null $line = null): self|null
    {
        $error = error_get_last();

        if (!is_array($error)) {
            return null;
        }

        if ($file !== null && $error['file'] !== $file) {
            return null;
        }

        if ($line !== null && $error['line'] !== $line) {
            return null;
        }

        return new self($error['message'], 0, $error['type'], $error['file'], $error['line']);
    }
}
