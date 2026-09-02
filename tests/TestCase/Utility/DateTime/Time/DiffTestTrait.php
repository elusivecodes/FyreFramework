<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Time;

use Fyre\Utility\DateTime\Time;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @phpstan-type DiffMethod 'diffInHours'|'diffInMinutes'|'diffInSeconds'
 */
trait DiffTestTrait
{
    /**
     * @return array<string, array{string, int[], int[], int}>
     */
    public static function diffProvider(): array
    {
        return [
            'hours' => ['diffInHours', [23], [12], 11],
            'minutes' => ['diffInMinutes', [16, 30], [12, 15], 255],
            'seconds' => ['diffInSeconds', [12, 30, 30], [12, 15, 15], 915],
        ];
    }

    public function testDiff(): void
    {
        $this->assertSame(
            500,
            Time::createFromArray([12, 30, 15, 500])
                ->diff(Time::createFromArray([12, 30, 15]))
        );
    }

    /**
     * @param DiffMethod $method
     * @param int[] $time1
     * @param int[] $time2
     */
    #[DataProvider('diffProvider')]
    public function testDiffIn(string $method, array $time1, array $time2, int $expected): void
    {
        $this->assertSame(
            $expected,
            Time::createFromArray($time1)->$method(Time::createFromArray($time2))
        );
    }

    public function testDiffInExact(): void
    {
        $this->assertSame(
            0,
            Time::createFromArray([1])
                ->diffInHours(Time::createFromArray([0, 1]), false)
        );
    }
}
