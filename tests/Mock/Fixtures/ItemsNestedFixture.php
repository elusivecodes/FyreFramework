<?php
declare(strict_types=1);

namespace Tests\Mock\Fixtures;

use Fyre\TestSuite\Fixture\Fixture;
use Override;

class ItemsNestedFixture extends Fixture
{
    #[Override]
    protected string $classAlias = 'Items';

    #[Override]
    protected iterable $data = [
        [
            'name' => 'Test 1',
            'children' => [
                [],
            ],
        ],
    ];
}
