<?php
declare(strict_types=1);

namespace Fyre\Http\Exceptions;

/**
 * Represents a Service Unavailable HTTP exception (503).
 */
class ServiceUnavailableException extends HttpException
{
    protected const DEFAULT_CODE = 503;

    protected const DEFAULT_MESSAGE = 'Service Unavailable';
}
