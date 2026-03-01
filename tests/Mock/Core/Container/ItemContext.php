<?php
declare(strict_types=1);

namespace Tests\Mock\Core\Container;

use Attribute;
use Fyre\Core\Container;
use Fyre\Core\ContextualAttribute;
use Override;

#[Attribute(Attribute::TARGET_PARAMETER)]
class ItemContext extends ContextualAttribute
{
    public function __construct(
        protected string $value
    ) {}

    #[Override]
    public function resolve(Container $container): Item
    {
        return $container->build(Item::class, ['value' => $this->value]);
    }
}
