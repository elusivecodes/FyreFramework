<?php
declare(strict_types=1);

namespace Fyre\Http\Session\Exceptions;

use RuntimeException;

/**
 * Represents session-related errors (e.g. attempting to write to a read-only session or
 * failing to start/close the session).
 */
class SessionException extends RuntimeException {}
