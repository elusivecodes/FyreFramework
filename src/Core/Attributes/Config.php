<?php
declare(strict_types=1);

namespace Fyre\Core\Attributes;

use Attribute;
use Fyre\Core\Config as CoreConfig;
use Fyre\Core\Container;
use Fyre\Core\ContextualAttribute;

/**
 * Resolves a config value for contextual injection.
 *
 * @extends ContextualAttribute<mixed>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Config extends ContextualAttribute
{
    /**
     * Constructs a Config attribute.
     *
     * @param string $key The key.
     */
    public function __construct(
        protected string $key
    ) {}

    /**
     * Retrieves a value from the config using "dot" notation.
     *
     * @param Container $container The Container.
     * @return mixed The config value.
     */
    public function resolve(Container $container): mixed
    {
        return $container->use(CoreConfig::class)->get($this->key);
    }
}
