<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;
use PHPUnit\Framework\Attributes\DataProvider;

trait AttributesWithTestTrait
{
    /**
     * @return array<string, array{int[], string, array<int|string>, string, int|string, bool}>
     */
    public static function transformationProvider(): array
    {
        return [
            'date' => [[2019, 1, 1], 'withDate', [15], 'toIsoString', '2019-01-15T00:00:00.000+00:00', true],
            'date wrap' => [[2019, 6, 1], 'withDate', [31], 'toIsoString', '2019-07-01T00:00:00.000+00:00', true],
            'day' => [[2019, 1, 1], 'withDay', [5], 'toIsoString', '2019-01-04T00:00:00.000+00:00', true],
            'day Monday' => [[2019, 1, 1], 'withDay', [1], 'toIsoString', '2018-12-31T00:00:00.000+00:00', true],
            'day of year' => [[2019, 1, 1], 'withDayOfYear', [235], 'toIsoString', '2019-08-23T00:00:00.000+00:00', true],
            'day of year wrap' => [[2019, 1, 1], 'withDayOfYear', [500], 'toIsoString', '2020-05-14T00:00:00.000+00:00', true],
            'day Sunday' => [[2019, 1, 1], 'withDay', [0], 'toIsoString', '2018-12-30T00:00:00.000+00:00', true],
            'day wrap' => [[2019, 1, 1], 'withDay', [12], 'toIsoString', '2019-01-11T00:00:00.000+00:00', true],
            'hours' => [[2019, 1, 1], 'withHours', [9], 'toIsoString', '2019-01-01T09:00:00.000+00:00', true],
            'hours 24 hour' => [[2019, 1, 1], 'withHours', [13], 'toIsoString', '2019-01-01T13:00:00.000+00:00', true],
            'hours with milliseconds' => [[2019, 1, 1], 'withHours', [0, 0, 0, 303], 'toIsoString', '2019-01-01T00:00:00.303+00:00', true],
            'hours with minutes' => [[2019, 1, 1], 'withHours', [0, 33], 'toIsoString', '2019-01-01T00:33:00.000+00:00', true],
            'hours with seconds' => [[2019, 1, 1], 'withHours', [0, 0, 23], 'toIsoString', '2019-01-01T00:00:23.000+00:00', true],
            'hours wrap' => [[2019, 1, 1], 'withHours', [33], 'toIsoString', '2019-01-02T09:00:00.000+00:00', true],
            'milliseconds' => [[2019, 1, 1], 'withMilliseconds', [220], 'toIsoString', '2019-01-01T00:00:00.220+00:00', true],
            'milliseconds wrap' => [[2019, 1, 1], 'withMilliseconds', [1220], 'toIsoString', '2019-01-01T00:00:01.220+00:00', true],
            'minutes' => [[2019, 1, 1], 'withMinutes', [15], 'toIsoString', '2019-01-01T00:15:00.000+00:00', true],
            'minutes with milliseconds' => [[2019, 1, 1], 'withMinutes', [0, 0, 320], 'toIsoString', '2019-01-01T00:00:00.320+00:00', true],
            'minutes with seconds' => [[2019, 1, 1], 'withMinutes', [0, 32], 'toIsoString', '2019-01-01T00:00:32.000+00:00', true],
            'minutes wrap' => [[2019, 1, 1], 'withMinutes', [75], 'toIsoString', '2019-01-01T01:15:00.000+00:00', true],
            'month' => [[2019, 1, 1], 'withMonth', [9], 'toIsoString', '2019-09-01T00:00:00.000+00:00', true],
            'month clamp' => [[2019, 1, 31], 'withMonth', [2], 'toIsoString', '2019-02-28T00:00:00.000+00:00', true],
            'month no clamp' => [[2019, 1, 31], 'withMonth', [2], 'toIsoString', '2019-03-03T00:00:00.000+00:00', false],
            'month with date' => [[2019, 1, 1], 'withMonth', [1, 23], 'toIsoString', '2019-01-23T00:00:00.000+00:00', true],
            'month wrap' => [[2019, 1, 1], 'withMonth', [15], 'toIsoString', '2020-03-01T00:00:00.000+00:00', true],
            'quarter' => [[2019, 1, 1], 'withQuarter', [2], 'toIsoString', '2019-04-01T00:00:00.000+00:00', true],
            'quarter wrap' => [[2019, 1, 1], 'withQuarter', [6], 'toIsoString', '2020-04-01T00:00:00.000+00:00', true],
            'seconds' => [[2019, 1, 1], 'withSeconds', [42], 'toIsoString', '2019-01-01T00:00:42.000+00:00', true],
            'seconds with milliseconds' => [[2019, 1, 1], 'withSeconds', [0, 550], 'toIsoString', '2019-01-01T00:00:00.550+00:00', true],
            'seconds wrap' => [[2019, 1, 1], 'withSeconds', [105], 'toIsoString', '2019-01-01T00:01:45.000+00:00', true],
            'time' => [[2018, 1, 1], 'withTime', [1546300800000], 'toIsoString', '2019-01-01T00:00:00.000+00:00', true],
            'timestamp' => [[2018, 1, 1], 'withTimestamp', [1546300800], 'toIsoString', '2019-01-01T00:00:00.000+00:00', true],
            'time zone' => [[2018, 1, 1], 'withTimeZone', ['Australia/Brisbane'], 'getTimeZone', 'Australia/Brisbane', true],
            'time zone from offset' => [[2018, 1, 1], 'withTimeZone', ['+10:00'], 'getTimeZoneOffset', -600, true],
            'time zone from offset without colon' => [[2018, 1, 1], 'withTimeZone', ['+1000'], 'getTimeZoneOffset', -600, true],
            'time zone offset' => [[2018, 1, 1], 'withTimeZoneOffset', [600], 'getTimeZoneOffset', 600, true],
            'week' => [[2019, 1, 1], 'withWeek', [23], 'toIsoString', '2019-06-04T00:00:00.000+00:00', true],
            'week day' => [[2019, 1, 1], 'withWeekDay', [6], 'toIsoString', '2019-01-04T00:00:00.000+00:00', true],
            'week day in month' => [[2019, 6, 1], 'withWeekDayInMonth', [4], 'toIsoString', '2019-06-22T00:00:00.000+00:00', true],
            'week day in month local' => [[2019, 6, 28], 'withWeekDayInMonth', [1], 'toIsoString', '2019-06-07T00:00:00.000+00:00', true],
            'week day Monday' => [[2019, 1, 1], 'withWeekDay', [2], 'toIsoString', '2018-12-31T00:00:00.000+00:00', true],
            'week day Sunday' => [[2019, 1, 1], 'withWeekDay', [1], 'toIsoString', '2018-12-30T00:00:00.000+00:00', true],
            'week day wrap' => [[2019, 1, 1], 'withWeekDay', [14], 'toIsoString', '2019-01-12T00:00:00.000+00:00', true],
            'week of month' => [[2019, 6, 1], 'withWeekOfMonth', [4], 'toIsoString', '2019-06-22T00:00:00.000+00:00', true],
            'week of month local' => [[2019, 6, 28], 'withWeekOfMonth', [1], 'toIsoString', '2019-05-31T00:00:00.000+00:00', true],
            'week uses week year' => [[2019, 12, 30], 'withWeek', [23], 'toIsoString', '2020-06-01T00:00:00.000+00:00', true],
            'week with days' => [[2019, 1, 1], 'withWeek', [1, 6], 'toIsoString', '2019-01-04T00:00:00.000+00:00', true],
            'week wrap' => [[2019, 1, 1], 'withWeek', [77], 'toIsoString', '2020-06-16T00:00:00.000+00:00', true],
            'week year' => [[2019, 1, 1], 'withWeekYear', [2018], 'toIsoString', '2018-01-02T00:00:00.000+00:00', true],
            'week year keeps week' => [[2019, 6, 1], 'withWeekYear', [2018], 'toIsoString', '2018-06-02T00:00:00.000+00:00', true],
            'week year with days' => [[2019, 1, 1], 'withWeekYear', [2018, 1, 6], 'toIsoString', '2018-01-05T00:00:00.000+00:00', true],
            'week year with week' => [[2019, 1, 1], 'withWeekYear', [2018, 14], 'toIsoString', '2018-04-03T00:00:00.000+00:00', true],
            'year' => [[2019, 1, 1], 'withYear', [2018], 'toIsoString', '2018-01-01T00:00:00.000+00:00', true],
            'year with days' => [[2019, 1, 1], 'withYear', [2018, 1, 16], 'toIsoString', '2018-01-16T00:00:00.000+00:00', true],
            'year with months' => [[2019, 1, 1], 'withYear', [2018, 6], 'toIsoString', '2018-06-01T00:00:00.000+00:00', true],
        ];
    }

    /**
     * @param int[] $parts
     * @param array<int|string> $arguments
     */
    #[DataProvider('transformationProvider')]
    public function testTransformation(
        array $parts,
        string $method,
        array $arguments,
        string $resultMethod,
        int|string $expected,
        bool $clampDates
    ): void {
        DateTime::withDateClamping($clampDates);

        try {
            $date1 = DateTime::createFromArray($parts);

            /** @var DateTime $date2 */
            $date2 = $date1->{$method}(...$arguments);
        } finally {
            DateTime::withDateClamping(true);
        }

        $this->assertNotSame(
            $date1,
            $date2
        );

        $this->assertSame(
            $expected,
            $date2->{$resultMethod}()
        );
    }
}
