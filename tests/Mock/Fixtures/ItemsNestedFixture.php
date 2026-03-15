<?php
declare(strict_types=1);

namespace Tests\Mock\Fixtures;

use Fyre\TestSuite\Fixture\Fixture;

class ItemsNestedFixture extends Fixture
{
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
