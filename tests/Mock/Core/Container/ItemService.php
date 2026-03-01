<?php
declare(strict_types=1);

namespace Tests\Mock\Core\Container;

class ItemService
{
    public function __construct(
        #[ItemContext('test')] protected Item $item
    ) {}

    public function getItem(): Item
    {
        return $this->item;
    }
}
