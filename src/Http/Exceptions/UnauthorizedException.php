<?php
declare(strict_types=1);

namespace Fyre\Http\Exceptions;

/**
 * Represents an Unauthorized HTTP exception (401).
 *
 * You can pass a `WWW-Authenticate` header via {@see HttpException::__construct()} `$headers`.
 */
class UnauthorizedException extends HttpException
{
    protected const DEFAULT_CODE = 401;

    protected const DEFAULT_MESSAGE = 'Unauthorized';
}
