<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;
use PHPUnit\Framework\Attributes\DataProvider;

trait ClampPercentTestTrait
{
    /**
     * @return array<string, array{int, int}>
     */
    public static function clampPercentProvider(): array
    {
        return [
            'within bounds' => [50, 50],
            'above bounds' => [150, 100],
            'below bounds' => [-50, 0],
        ];
    }

    #[DataProvider('clampPercentProvider')]
    public function testClampPercent(int $number, int $expected): void
    {
        $this->assertSame(
            $expected,
            Math::clampPercent($number)
        );
    }
}
