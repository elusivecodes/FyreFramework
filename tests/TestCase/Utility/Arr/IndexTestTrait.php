<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait IndexTestTrait
{
    public function testIndex(): void
    {
        $this->assertSame(
            [
                1 => [
                    'a' => 1,
                    'b' => 'x',
                ],
                2 => [
                    'a' => 2,
                    'b' => 'y',
                ],
                3 => [
                    'a' => 3,
                    'b' => 'z',
                ],
            ],
            Arr::index([
                ['a' => 1, 'b' => 'x'],
                ['a' => 2, 'b' => 'y'],
                ['a' => 3, 'b' => 'z'],
            ], 'a')
        );
    }
}
