<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Time;

use Fyre\Utility\DateTime\Time;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @phpstan-type DiffInMethod 'diffInHours'|'diffInMinutes'|'diffInSeconds'
 */
trait DiffTestTrait
{
    /**
     * @return array<string, array{DiffInMethod, int[], int[], bool, int}>
     */
    public static function diffInProvider(): array
    {
        return [
            'hours' => ['diffInHours', [23], [12], true, 11],
            'hours negative' => ['diffInHours', [12], [23], true, -11],
            'hours relative' => ['diffInHours', [1, 0], [0, 1], true, 1],
            'hours exact' => ['diffInHours', [1, 0], [0, 1], false, 0],
            'minutes' => ['diffInMinutes', [16, 30], [12, 15], true, 255],
            'minutes negative' => ['diffInMinutes', [12, 15], [12, 30], true, -15],
            'minutes relative' => ['diffInMinutes', [0, 1, 0], [0, 0, 1], true, 1],
            'minutes exact' => ['diffInMinutes', [0, 1, 0], [0, 0, 1], false, 0],
            'seconds' => ['diffInSeconds', [12, 50, 30], [12, 30, 15], true, 1215],
            'seconds negative' => ['diffInSeconds', [12, 30, 15], [12, 30, 30], true, -15],
            'seconds relative' => ['diffInSeconds', [0, 0, 1, 0], [0, 0, 0, 1], true, 1],
            'seconds exact' => ['diffInSeconds', [0, 0, 1, 0], [0, 0, 0, 1], false, 0],
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
     * @param DiffInMethod $method
     * @param int[] $time1
     * @param int[] $time2
     */
    #[DataProvider('diffInProvider')]
    public function testDiffIn(
        string $method,
        array $time1,
        array $time2,
        bool $relative,
        int $expected
    ): void {
        $this->assertSame(
            $expected,
            Time::createFromArray($time1)->$method(
                Time::createFromArray($time2),
                $relative
            )
        );
    }
}
