<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait MapTestTrait
{
    public function testMap(): void
    {
        $this->assertSame(
            71.875,
            Math::map(25, 10, 50, 25, 150)
        );
    }
}
