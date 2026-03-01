<?php
declare(strict_types=1);

namespace Fyre\Http\Exceptions;

/**
 * Represents a Conflict HTTP exception (409).
 */
class ConflictException extends HttpException
{
    protected const DEFAULT_CODE = 409;

    protected const DEFAULT_MESSAGE = 'Conflict';
}
