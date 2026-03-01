<?php
declare(strict_types=1);

namespace Fyre\Core\Attributes;

use Attribute;
use Fyre\Auth\Auth;
use Fyre\Core\Container;
use Fyre\Core\ContextualAttribute;
use Fyre\ORM\Entity;

/**
 * Injects the current authenticated user.
 *
 * @extends ContextualAttribute<Entity|null>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class CurrentUser extends ContextualAttribute
{
    /**
     * Returns the current user.
     *
     * @param Container $container The Container.
     * @return Entity|null The Entity instance for the current user or null if no user is logged in.
     */
    public function resolve(Container $container): Entity|null
    {
        return $container->use(Auth::class)->user();
    }
}
