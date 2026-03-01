<?php
declare(strict_types=1);

namespace Fyre\Http\Client\Exceptions;

use Override;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use RuntimeException;
use Throwable;

/**
 * Represents an exception thrown when a request could not be sent due to a client-side
 * error (e.g. invalid request configuration). Implements PSR-18
 * {@see RequestExceptionInterface} and exposes the originating request.
 */
class RequestException extends RuntimeException implements RequestExceptionInterface
{
    /**
     * Constructs a RequestException.
     *
     * @param string $message The message.
     * @param RequestInterface $request The Request.
     * @param int $code The error code.
     * @param Throwable|null $previous The previous exception.
     */
    public function __construct(
        string $message,
        protected RequestInterface $request,
        int $code = 0,
        Throwable|null $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
