<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Date;

use Fyre\Utility\DateTime\Date;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @phpstan-type DiffMethod 'diffInDays'|'diffInMonths'|'diffInWeeks'|'diffInYears'
 */
trait DiffTestTrait
{
    /**
     * @return array<string, array{string, int[], int[], int}>
     */
    public static function diffProvider(): array
    {
        return [
            'days' => ['diffInDays', [2018, 6, 23], [2018, 6, 15], 8],
            'months' => ['diffInMonths', [2018, 8, 23], [2018, 6, 15], 2],
            'weeks' => ['diffInWeeks', [2018, 6, 29], [2018, 6, 15], 2],
            'years' => ['diffInYears', [2020, 6, 23], [2018, 6, 15], 2],
        ];
    }

    public function testDiff(): void
    {
        $this->assertSame(
            691200000,
            Date::createFromArray([2018, 6, 23])
                ->diff(Date::createFromArray([2018, 6, 15]))
        );
    }

    /**
     * @param DiffMethod $method
     * @param int[] $date1
     * @param int[] $date2
     */
    #[DataProvider('diffProvider')]
    public function testDiffIn(string $method, array $date1, array $date2, int $expected): void
    {
        $this->assertSame(
            $expected,
            Date::createFromArray($date1)->$method(Date::createFromArray($date2))
        );
    }

    public function testDiffInExact(): void
    {
        $this->assertSame(
            1,
            Date::createFromArray([2018, 1, 2])
                ->diffInDays(Date::createFromArray([2018, 1, 1]), false)
        );
    }
}
