<?php
declare(strict_types=1);

namespace Fyre\Http\Exceptions;

/**
 * Represents a Too Many Requests HTTP exception (429).
 *
 * You can pass a `Retry-After` header via {@see HttpException::__construct()} `$headers`.
 */
class TooManyRequestsException extends HttpException
{
    protected const DEFAULT_CODE = 429;

    protected const DEFAULT_MESSAGE = 'Too Many Requests';
}
