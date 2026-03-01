<?php
declare(strict_types=1);

namespace Fyre\Http\Exceptions;

/**
 * Represents a Forbidden HTTP exception (403).
 */
class ForbiddenException extends HttpException
{
    protected const DEFAULT_CODE = 403;

    protected const DEFAULT_MESSAGE = 'Forbidden';
}
