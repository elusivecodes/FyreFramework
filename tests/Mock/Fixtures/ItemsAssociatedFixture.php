<?php
declare(strict_types=1);

namespace Tests\Mock\Fixtures;

use Fyre\TestSuite\Fixture\Fixture;
use Override;

class ItemsAssociatedFixture extends Fixture
{
    #[Override]
    protected array|string|null $associated = 'Children';

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
