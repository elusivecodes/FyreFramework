<?php
declare(strict_types=1);

namespace Fyre\Http\Exceptions;

/**
 * Represents a Not Found HTTP exception (404).
 */
class NotFoundException extends HttpException
{
    protected const DEFAULT_CODE = 404;

    protected const DEFAULT_MESSAGE = 'Not Found';
}
