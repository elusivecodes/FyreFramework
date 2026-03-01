<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;

trait ForgetDotTestTrait
{
    public function testForgetDot(): void
    {
        $this->assertSame(
            [
                'a' => 1,
                'b' => [
                    'c' => 2,
                ],
            ],
            Arr::forgetDot(['a' => 1, 'b' => ['c' => 2, 'd' => 3]], 'b.d')
        );
    }

    public function testForgetDotMissing(): void
    {
        $this->assertSame(
            [
                'a' => 1,
                'b' => [
                    'c' => 2,
                ],
            ],
            Arr::forgetDot(['a' => 1, 'b' => ['c' => 2]], 'b.d')
        );
    }
}
