<?php
declare(strict_types=1);

namespace Fyre\Router\Routes;

use Closure;
use Fyre\Core\Container;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Router\Exceptions\RouterException;
use Fyre\Router\Route;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use ReflectionClass;

use function array_shift;
use function class_exists;
use function method_exists;
use function sprintf;

/**
 * Dispatches to a controller action.
 */
class ControllerRoute extends Route
{
    use MacroTrait;

    protected string $action;

    protected string $controller;

    /**
     * Constructs a ControllerRoute.
     *
     * @param Container $container The Container.
     * @param array{0: class-string, 1?: string}|string $destination The destination.
     * @param string $path The path.
     * @param string|null $scheme The scheme.
     * @param string|null $host The host.
     * @param int|null $port The port.
     * @param string[]|null $methods The methods.
     * @param array<Closure|MiddlewareInterface|string> $middleware The middleware.
     * @param array<string, string> $placeholders The placeholders.
     */
    public function __construct(
        Container $container,
        array|string $destination,
        string $path = '',
        string|null $scheme = null,
        string|null $host = null,
        int|null $port = null,
        array|null $methods = null,
        array $middleware = [],
        array $placeholders = []
    ) {
        parent::__construct(
            $container,
            $destination,
            $path,
            $scheme,
            $host,
            $port,
            $methods,
            $middleware,
            $placeholders
        );

        $destination = (array) $this->destination;

        $this->controller = array_shift($destination);
        $this->action = array_shift($destination) ?? 'index';
    }

    /**
     * Returns the route controller action.
     *
     * @return string The route controller action.
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Returns the route controller class name.
     *
     * @return string The route controller class name.
     */
    public function getController(): string
    {
        return $this->controller;
    }

    /**
     * {@inheritDoc}
     *
     * Note: This will return an empty array if the controller class or action method does
     * not exist.
     */
    #[Override]
    public function getParameters(): array
    {
        if (!class_exists($this->controller) || !method_exists($this->controller, $this->action)) {
            return [];
        }

        return new ReflectionClass($this->controller)
            ->getMethod($this->action)
            ->getParameters();
    }

    /**
     * {@inheritDoc}
     *
     * Note: The controller is built via the container with the current request, and the
     * action is invoked via the container so route arguments can be mapped by name.
     *
     * @throws RouterException If the controller class or method are not valid.
     */
    #[Override]
    protected function process(ServerRequestInterface $request): ResponseInterface|string
    {
        if (!class_exists($this->controller)) {
            throw new RouterException(sprintf(
                'Controller `%s` does not exist.',
                $this->controller
            ));
        }

        if (!method_exists($this->controller, $this->action)) {
            throw new RouterException(sprintf(
                'Controller method `%s::%s` does not exist.',
                $this->controller,
                $this->action
            ));
        }

        $controller = $this->container->build($this->controller, [
            'request' => $request,
        ]);

        return $this->container->call(
            [$controller, $this->action],
            $request->getAttribute('routeArguments', [])
        );
    }
}
