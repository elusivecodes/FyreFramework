<?php
declare(strict_types=1);

namespace Fyre\Core;

/**
 * Resolves contextual values from the container.
 *
 * @template TReturn
 */
abstract class ContextualAttribute
{
    /**
     * Resolves a value from the container.
     *
     * @param Container $container The Container.
     * @return TReturn The resolved value.
     */
    abstract public function resolve(Container $container): mixed;
}
