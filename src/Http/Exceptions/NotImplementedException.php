<?php
declare(strict_types=1);

namespace Fyre\Http\Exceptions;

/**
 * Represents a Not Implemented HTTP exception (501).
 */
class NotImplementedException extends HttpException
{
    protected const DEFAULT_CODE = 501;

    protected const DEFAULT_MESSAGE = 'Not Implemented';
}
