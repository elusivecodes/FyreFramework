<?php
declare(strict_types=1);

namespace Fyre\Router\Routes;

use Closure;
use Fyre\Core\Container;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Http\RedirectResponse;
use Fyre\Router\Exceptions\RouterException;
use Fyre\Router\Route;
use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

use function assert;
use function explode;
use function is_string;
use function preg_replace_callback;
use function sprintf;
use function str_contains;
use function str_ends_with;
use function substr;

/**
 * Issues an HTTP redirect.
 */
class RedirectRoute extends Route
{
    use MacroTrait;

    /**
     * Constructs a RedirectRoute.
     *
     * @param Container $container The Container.
     * @param string $destination The destination.
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
        string $destination,
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
    }

    /**
     * {@inheritDoc}
     *
     * Note: Placeholder values are substituted from the current route arguments.
     *
     * @throws RouterException If a route parameter is missing.
     */
    #[Override]
    protected function process(ServerRequestInterface $request): ResponseInterface
    {
        assert(is_string($this->destination));

        $arguments = $request->getAttribute('routeArguments', []);

        $destination = (string) preg_replace_callback(
            '/\/\{([^\}]+)\}/',
            static function(array $match) use ($arguments): string {
                $name = $match[1];

                $optional = false;
                if (str_ends_with($name, '?')) {
                    $name = substr($name, 0, -1);
                    $optional = true;
                }

                if (str_contains($name, ':')) {
                    [$name, $field] = explode(':', $name, 2);
                }

                $arguments[$name] ??= null;

                if (!$optional && $arguments[$name] === null) {
                    throw new RouterException(sprintf(
                        'Router parameter `%s` is missing.',
                        $name
                    ));
                }

                if ($arguments[$name] === null) {
                    return '';
                }

                return '/'.$arguments[$name];
            },
            $this->destination
        );

        return new RedirectResponse($destination);
    }
}
