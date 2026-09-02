<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Date;

use Fyre\Utility\DateTime\Date;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @phpstan-type DiffInMethod 'diffInDays'|'diffInMonths'|'diffInWeeks'|'diffInYears'
 */
trait DiffTestTrait
{
    /**
     * @return array<string, array{DiffInMethod, int[], int[], bool, int}>
     */
    public static function diffInProvider(): array
    {
        return [
            'days' => ['diffInDays', [2018, 8, 23], [2018, 6, 15], true, 69],
            'days negative' => ['diffInDays', [2018, 6, 15], [2018, 6, 23], true, -8],
            'months' => ['diffInMonths', [2018, 9], [2016, 6], true, 27],
            'months negative' => ['diffInMonths', [2018, 6], [2018, 9], true, -3],
            'months relative' => ['diffInMonths', [2018, 2, 1], [2018, 1, 2], true, 1],
            'months exact' => ['diffInMonths', [2018, 2, 1], [2018, 1, 2], false, 0],
            'weeks' => ['diffInWeeks', [2018, 8, 23], [2018, 6, 15], true, 10],
            'weeks negative' => ['diffInWeeks', [2018, 5, 15], [2018, 6, 23], true, -5],
            'years' => ['diffInYears', [2018], [2016], true, 2],
            'years negative' => ['diffInYears', [2016], [2018], true, -2],
            'years relative' => ['diffInYears', [2018, 1], [2017, 2], true, 1],
            'years exact' => ['diffInYears', [2018, 1], [2017, 2], false, 0],
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
     * @param DiffInMethod $method
     * @param int[] $date1
     * @param int[] $date2
     */
    #[DataProvider('diffInProvider')]
    public function testDiffIn(
        string $method,
        array $date1,
        array $date2,
        bool $relative,
        int $expected
    ): void {
        $this->assertSame(
            $expected,
            Date::createFromArray($date1)->$method(
                Date::createFromArray($date2),
                $relative
            )
        );
    }
}
