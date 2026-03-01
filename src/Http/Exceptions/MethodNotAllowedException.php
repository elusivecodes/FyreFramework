<?php
declare(strict_types=1);

namespace Fyre\Http\Exceptions;

/**
 * Represents a Method Not Allowed HTTP exception (405).
 *
 * You can pass an `Allow` header via {@see HttpException::__construct()} `$headers` to
 * indicate which methods are permitted.
 */
class MethodNotAllowedException extends HttpException
{
    protected const DEFAULT_CODE = 405;

    protected const DEFAULT_MESSAGE = 'Method Not Allowed';
}
