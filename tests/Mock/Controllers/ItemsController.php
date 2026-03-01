<?php
declare(strict_types=1);

namespace Tests\Mock\Controllers;

use Tests\Mock\Entities\Item;

class ItemsController
{
    public function index(Item $item): string
    {
        return $item->name;
    }

    public function test(Item|null $item = null): string
    {
        return '';
    }
}
