<?php
declare(strict_types=1);

namespace Tests\Mock\Fixtures;

use Fyre\TestSuite\Fixture\Fixture;

class ItemsAssociatedFixture extends Fixture
{
    protected array|string|null $associated = 'Children';

    protected string $classAlias = 'Items';

    protected iterable $data = [
        [
            'name' => 'Test 1',
            'children' => [
                [],
            ],
        ],
    ];
}
