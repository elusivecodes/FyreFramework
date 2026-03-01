<?php
declare(strict_types=1);

namespace Fyre\Core\Attributes;

use Attribute;
use Fyre\Cache\CacheManager;
use Fyre\Cache\Cacher;
use Fyre\Core\Container;
use Fyre\Core\ContextualAttribute;

/**
 * Resolves a cache handler for contextual injection.
 *
 * @extends ContextualAttribute<Cacher>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Cache extends ContextualAttribute
{
    /**
     * Constructs a Cache attribute.
     *
     * @param string $key The cache key.
     */
    public function __construct(
        protected string $key = CacheManager::DEFAULT
    ) {}

    /**
     * Resolves the Cacher for contextual injection.
     *
     * @param Container $container The Container.
     * @return Cacher The Cacher instance for the cache key.
     */
    public function resolve(Container $container): Cacher
    {
        return $container->use(CacheManager::class)->use($this->key);
    }
}
