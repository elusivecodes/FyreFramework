<?php
declare(strict_types=1);

namespace Fyre\Http\Exceptions;

/**
 * Represents a Gone HTTP exception (410).
 */
class GoneException extends HttpException
{
    protected const DEFAULT_CODE = 410;

    protected const DEFAULT_MESSAGE = 'Gone';
}
