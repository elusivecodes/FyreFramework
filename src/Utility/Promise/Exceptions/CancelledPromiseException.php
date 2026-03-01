<?php
declare(strict_types=1);

namespace Fyre\Utility\Promise\Exceptions;

use Exception;
use Throwable;

/**
 * Represents a cancelled promise exception.
 *
 * This is typically thrown when an AsyncPromise is cancelled (manually or due to exceeding the maximum runtime).
 */
class CancelledPromiseException extends Exception
{
    public const DEFAULT_MESSAGE = 'Promise was cancelled.';

    /**
     * Constructs a CancelledPromiseException.
     *
     * @param string|null $message The message.
     * @param int $code The error code.
     * @param Throwable|null $previous The previous exception.
     */
    public function __construct(string|null $message = null, int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct($message ?? static::DEFAULT_MESSAGE, $code, $previous);
    }
}
