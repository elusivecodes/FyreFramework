<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;
use PHPUnit\Framework\Attributes\DataProvider;

trait AbsTestTrait
{
    /**
     * @return array<string, array{float|int, float|int}>
     */
    public static function absProvider(): array
    {
        return [
            'positive integer' => [1, 1],
            'negative float' => [-.5, .5],
            'negative integer' => [-1, 1],
        ];
    }

    #[DataProvider('absProvider')]
    public function testAbs(float|int $number, float|int $expected): void
    {
        $this->assertSame(
            $expected,
            Math::abs($number)
        );
    }
}
