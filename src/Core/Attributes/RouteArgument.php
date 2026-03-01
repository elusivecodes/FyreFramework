<?php
declare(strict_types=1);

namespace Fyre\Core\Attributes;

use Attribute;
use Fyre\Core\Container;
use Fyre\Core\ContextualAttribute;
use Fyre\Http\ServerRequest;

/**
 * Injects a route argument value.
 *
 * @extends ContextualAttribute<mixed>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class RouteArgument extends ContextualAttribute
{
    /**
     * Constructs a RouteArgument attribute.
     *
     * @param string $name The name.
     */
    public function __construct(
        protected string $name
    ) {}

    /**
     * Returns a route argument value.
     *
     * @param Container $container The Container.
     * @return mixed The route argument value.
     */
    public function resolve(Container $container): mixed
    {
        return $container->use(ServerRequest::class)
            ->getAttribute('routeArguments')[$this->name] ?? null;
    }
}
