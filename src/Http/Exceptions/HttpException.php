<?php
declare(strict_types=1);

namespace Fyre\Http\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Provides a base exception type used to represent an HTTP error response.
 *
 * The exception code is used as the HTTP status code, and optional headers can be attached
 * for response construction (e.g. `Allow` for 405).
 */
abstract class HttpException extends RuntimeException
{
    protected const DEFAULT_CODE = 500;

    protected const DEFAULT_MESSAGE = 'Internal Server Error';

    /**
     * Constructs an HttpException.
     *
     * @param string|null $message The message.
     * @param int|null $code The HTTP status code.
     * @param Throwable|null $previous The previous exception.
     * @param array<string, mixed> $headers The additional headers for the response.
     */
    public function __construct(
        string|null $message = null,
        int|null $code = null,
        Throwable|null $previous = null,
        protected array $headers = []
    ) {
        parent::__construct($message ?? static::DEFAULT_MESSAGE, $code ?? static::DEFAULT_CODE, $previous);
    }

    /**
     * Returns the response headers.
     *
     * @return array<string, mixed> The response headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
