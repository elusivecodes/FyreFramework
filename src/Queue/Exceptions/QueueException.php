<?php
declare(strict_types=1);

namespace Fyre\Queue\Exceptions;

use RuntimeException;

/**
 * Base exception for queue-related errors.
 */
class QueueException extends RuntimeException {}
