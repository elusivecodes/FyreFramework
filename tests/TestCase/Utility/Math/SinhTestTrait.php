<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;

trait SinhTestTrait
{
    public function testSinh(): void
    {
        $this->assertSame(
            .5210953054937474,
            Math::sinh(.5)
        );
    }
}
