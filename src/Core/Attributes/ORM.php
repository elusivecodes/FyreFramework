<?php
declare(strict_types=1);

namespace Fyre\Core\Attributes;

use Attribute;
use Fyre\Core\Container;
use Fyre\Core\ContextualAttribute;
use Fyre\ORM\Model;
use Fyre\ORM\ModelRegistry;

/**
 * Resolves an ORM model for contextual injection.
 *
 * @extends ContextualAttribute<Model>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class ORM extends ContextualAttribute
{
    /**
     * Constructs an ORM attribute.
     *
     * @param string $alias The alias.
     */
    public function __construct(
        protected string $alias
    ) {}

    /**
     * Resolves the Model for contextual injection.
     *
     * @param Container $container The Container.
     * @return Model The Model instance for the alias.
     */
    public function resolve(Container $container): Model
    {
        return $container->use(ModelRegistry::class)->use($this->alias);
    }
}
