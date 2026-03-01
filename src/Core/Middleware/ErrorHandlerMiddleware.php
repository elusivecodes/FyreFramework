<?php
declare(strict_types=1);

namespace Fyre\Core\Middleware;

use Fyre\Core\ErrorHandler;
use Fyre\Core\Traits\DebugTrait;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Handles exceptions during request processing.
 */
class ErrorHandlerMiddleware implements MiddlewareInterface
{
    use DebugTrait;

    /**
     * Constructs an ErrorHandlerMiddleware.
     *
     * @param ErrorHandler $errorHandler The ErrorHandler.
     */
    public function __construct(
        protected ErrorHandler $errorHandler
    ) {}

    /**
     * {@inheritDoc}
     *
     * Catches any thrown {@see Throwable} and delegates to {@see ErrorHandler::render()}.
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (Throwable $e) {
            return $this->errorHandler->render($e);
        }
    }
}
