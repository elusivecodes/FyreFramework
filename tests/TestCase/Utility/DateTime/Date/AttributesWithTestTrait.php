<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Date;

use Fyre\Utility\DateTime\Date;
use PHPUnit\Framework\Attributes\DataProvider;

trait AttributesWithTestTrait
{
    /**
     * @return array<string, array{int[], string, array<int>, string, bool}>
     */
    public static function transformationProvider(): array
    {
        return [
            'date' => [[2019, 1, 1], 'withDate', [15], '2019-01-15', true],
            'day' => [[2019, 1, 1], 'withDay', [5], '2019-01-04', true],
            'day of year' => [[2019, 1, 1], 'withDayOfYear', [235], '2019-08-23', true],
            'month clamp' => [[2019, 1, 31], 'withMonth', [2], '2019-02-28', true],
            'month no clamp' => [[2019, 1, 31], 'withMonth', [2], '2019-03-03', false],
            'month with date' => [[2019, 1, 1], 'withMonth', [1, 23], '2019-01-23', true],
            'quarter' => [[2019, 1, 1], 'withQuarter', [2], '2019-04-01', true],
            'week day' => [[2019, 1, 1], 'withWeekDay', [6], '2019-01-04', true],
            'week day in month' => [[2019, 6, 1], 'withWeekDayInMonth', [4], '2019-06-22', true],
            'week of month' => [[2019, 6, 1], 'withWeekOfMonth', [4], '2019-06-22', true],
            'week with days' => [[2019, 1, 1], 'withWeek', [1, 6], '2019-01-04', true],
            'week year with days' => [[2019, 1, 1], 'withWeekYear', [2018, 1, 6], '2018-01-05', true],
            'year with days' => [[2019, 1, 1], 'withYear', [2018, 1, 16], '2018-01-16', true],
        ];
    }

    /**
     * @param int[] $parts
     * @param int[] $arguments
     */
    #[DataProvider('transformationProvider')]
    public function testTransformation(
        array $parts,
        string $method,
        array $arguments,
        string $expected,
        bool $clampDates
    ): void {
        Date::withDateClamping($clampDates);

        try {
            $date1 = Date::createFromArray($parts);

            /** @var Date $date2 */
            $date2 = $date1->$method(...$arguments);
        } finally {
            Date::withDateClamping(true);
        }

        $this->assertNotSame(
            $date1,
            $date2
        );
        $this->assertSame(
            $expected,
            $date2->toIsoString()
        );
    }

    public function testWithLocale(): void
    {
        $date = Date::createFromArray([2019, 1, 1]);
        $result = $date->withLocale('ar-eg');

        $this->assertNotSame(
            $date,
            $result
        );
        $this->assertArraysAreIdentical(
            ['2019-01-01', 'UTC', 'ar-eg'],
            [$result->toIsoString(), $result->getTimeZone(), $result->getLocale()]
        );
    }
}
