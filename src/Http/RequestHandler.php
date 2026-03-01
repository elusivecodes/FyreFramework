<?php
declare(strict_types=1);

namespace Fyre\Http;

use Fyre\Core\Container;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Executes middleware from a {@see MiddlewareQueue} and resolves aliases/groups via
 * {@see MiddlewareRegistry}. When the queue is exhausted, an optional fallback handler is
 * invoked; otherwise a `204 No Content` {@see ClientResponse} is returned.
 */
class RequestHandler implements RequestHandlerInterface
{
    /**
     * Constructs a RequestHandler.
     *
     * @param Container $container The Container.
     * @param MiddlewareRegistry $middlewareRegistry The MiddlewareRegistry.
     * @param MiddlewareQueue $queue The MiddlewareQueue.
     * @param RequestHandlerInterface|null $fallbackHandler The fallback RequestHandler.
     */
    public function __construct(
        protected Container $container,
        protected MiddlewareRegistry $middlewareRegistry,
        protected MiddlewareQueue $queue,
        protected RequestHandlerInterface|null $fallbackHandler = null
    ) {}

    /**
     * Handles the next middleware in the queue.
     *
     * Note: If the incoming request is a {@see ServerRequest}, it is registered into the
     * container as the current instance of {@see ServerRequest::class} for downstream
     * resolution.
     *
     * @param ServerRequestInterface $request The ServerRequest.
     * @return ResponseInterface The Response to return.
     */
    #[Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($request instanceof ServerRequest) {
            $this->container->instance(ServerRequest::class, $request);
        }

        if (!$this->queue->valid()) {
            return $this->fallbackHandler ?
                $this->fallbackHandler->handle($request) :
                $this->container->build(ClientResponse::class, [
                    'options' => [
                        'statusCode' => 204,
                    ],
                ]);
        }

        $middleware = $this->middlewareRegistry->resolve($this->queue->current());

        $this->queue->next();

        if ($middleware instanceof MiddlewareInterface) {
            $middleware = $middleware->process(...);
        }

        return $middleware($request, $this);
    }
}
