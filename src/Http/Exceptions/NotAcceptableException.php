<?php
declare(strict_types=1);

namespace Fyre\Http\Exceptions;

/**
 * Represents a Not Acceptable HTTP exception (406).
 */
class NotAcceptableException extends HttpException
{
    protected const DEFAULT_CODE = 406;

    protected const DEFAULT_MESSAGE = 'Not Acceptable';
}
