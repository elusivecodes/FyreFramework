<?php
declare(strict_types=1);

namespace Tests\Mock\Fixtures;

use Fyre\TestSuite\Fixture\Fixture;

class ItemsFixture extends Fixture
{
    protected iterable $data = [
        [
            'name' => 'Test 1',
        ],
        [
            'name' => 'Test 2',
        ],
    ];
}
