<?php
declare(strict_types=1);

namespace Fyre\DB\Exceptions;

use Throwable;

/**
 * Represents an exception thrown when a database operation is attempted without a valid connection.
 */
class MissingConnectionException extends DbException
{
    /**
     * Constructs a MissingConnectionException.
     *
     * @param string $message The message.
     * @param int $code The error code.
     * @param Throwable|null $previous The previous exception.
     */
    public function __construct(string $message = 'Database is not connected.', int $code = 0, Throwable|null $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
