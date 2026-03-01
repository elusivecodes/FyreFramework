<?php
declare(strict_types=1);

namespace Fyre\Http\Exceptions;

/**
 * Represents a Bad Request HTTP exception (400).
 */
class BadRequestException extends HttpException
{
    protected const DEFAULT_CODE = 400;

    protected const DEFAULT_MESSAGE = 'Bad Request';
}
