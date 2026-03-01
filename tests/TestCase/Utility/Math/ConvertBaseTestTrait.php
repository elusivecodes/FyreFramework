<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait ConvertBaseTestTrait
{
    public function testConvertBase(): void
    {
        $this->assertSame(
            '2b',
            Math::convertBase(43, 10, 16)
        );
    }

    public function testConvertBaseString(): void
    {
        $this->assertSame(
            '43',
            Math::convertBase('2b', 16, 10)
        );
    }
}
