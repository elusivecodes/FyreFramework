<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;
use PHPUnit\Framework\Attributes\DataProvider;

trait ManipulateTestTrait
{
    /**
     * @return array<string, array{int[], string, int[], string}>
     */
    public static function manipulateProvider(): array
    {
        return [
            'add day' => [[2018], 'addDay', [], '2018-01-02T00:00:00.000+00:00'],
            'add days' => [[2018], 'addDays', [2], '2018-01-03T00:00:00.000+00:00'],
            'add hour' => [[2018], 'addHour', [], '2018-01-01T01:00:00.000+00:00'],
            'add hours' => [[2018], 'addHours', [2], '2018-01-01T02:00:00.000+00:00'],
            'add minute' => [[2018], 'addMinute', [], '2018-01-01T00:01:00.000+00:00'],
            'add minutes' => [[2018], 'addMinutes', [2], '2018-01-01T00:02:00.000+00:00'],
            'add month' => [[2018], 'addMonth', [], '2018-02-01T00:00:00.000+00:00'],
            'add months' => [[2018], 'addMonths', [2], '2018-03-01T00:00:00.000+00:00'],
            'add second' => [[2018], 'addSecond', [], '2018-01-01T00:00:01.000+00:00'],
            'add seconds' => [[2018], 'addSeconds', [2], '2018-01-01T00:00:02.000+00:00'],
            'add week' => [[2018], 'addWeek', [], '2018-01-08T00:00:00.000+00:00'],
            'add weeks' => [[2018], 'addWeeks', [2], '2018-01-15T00:00:00.000+00:00'],
            'add year' => [[2018], 'addYear', [], '2019-01-01T00:00:00.000+00:00'],
            'add years' => [[2018], 'addYears', [2], '2020-01-01T00:00:00.000+00:00'],
            'end of day' => [[2018, 6, 15, 11, 30, 30, 500], 'endOfDay', [], '2018-06-15T23:59:59.999+00:00'],
            'end of hour' => [[2018, 6, 15, 11, 30, 30, 500], 'endOfHour', [], '2018-06-15T11:59:59.999+00:00'],
            'end of minute' => [[2018, 6, 15, 11, 30, 30, 500], 'endOfMinute', [], '2018-06-15T11:30:59.999+00:00'],
            'end of month' => [[2018, 6, 15, 11, 30, 30, 500], 'endOfMonth', [], '2018-06-30T23:59:59.999+00:00'],
            'end of quarter' => [[2018, 8, 15, 11, 30, 30, 500], 'endOfQuarter', [], '2018-09-30T23:59:59.999+00:00'],
            'end of second' => [[2018, 6, 15, 11, 30, 30, 500], 'endOfSecond', [], '2018-06-15T11:30:30.999+00:00'],
            'end of week' => [[2018, 6, 15, 11, 30, 30, 500], 'endOfWeek', [], '2018-06-16T23:59:59.999+00:00'],
            'end of year' => [[2018, 6, 15, 11, 30, 30, 500], 'endOfYear', [], '2018-12-31T23:59:59.999+00:00'],
            'start of day' => [[2018, 6, 15, 11, 30, 30, 500], 'startOfDay', [], '2018-06-15T00:00:00.000+00:00'],
            'start of hour' => [[2018, 6, 15, 11, 30, 30, 500], 'startOfHour', [], '2018-06-15T11:00:00.000+00:00'],
            'start of minute' => [[2018, 6, 15, 11, 30, 30, 500], 'startOfMinute', [], '2018-06-15T11:30:00.000+00:00'],
            'start of month' => [[2018, 6, 15, 11, 30, 30, 500], 'startOfMonth', [], '2018-06-01T00:00:00.000+00:00'],
            'start of quarter' => [[2018, 8, 15, 11, 30, 30, 500], 'startOfQuarter', [], '2018-07-01T00:00:00.000+00:00'],
            'start of second' => [[2018, 6, 15, 11, 30, 30, 500], 'startOfSecond', [], '2018-06-15T11:30:30.000+00:00'],
            'start of week' => [[2018, 6, 15, 11, 30, 30, 500], 'startOfWeek', [], '2018-06-10T00:00:00.000+00:00'],
            'start of year' => [[2018, 6, 15, 11, 30, 30, 500], 'startOfYear', [], '2018-01-01T00:00:00.000+00:00'],
            'sub day' => [[2018], 'subDay', [], '2017-12-31T00:00:00.000+00:00'],
            'sub days' => [[2018], 'subDays', [2], '2017-12-30T00:00:00.000+00:00'],
            'sub hour' => [[2018], 'subHour', [], '2017-12-31T23:00:00.000+00:00'],
            'sub hours' => [[2018], 'subHours', [2], '2017-12-31T22:00:00.000+00:00'],
            'sub minute' => [[2018], 'subMinute', [], '2017-12-31T23:59:00.000+00:00'],
            'sub minutes' => [[2018], 'subMinutes', [2], '2017-12-31T23:58:00.000+00:00'],
            'sub month' => [[2018], 'subMonth', [], '2017-12-01T00:00:00.000+00:00'],
            'sub months' => [[2018], 'subMonths', [2], '2017-11-01T00:00:00.000+00:00'],
            'sub second' => [[2018], 'subSecond', [], '2017-12-31T23:59:59.000+00:00'],
            'sub seconds' => [[2018], 'subSeconds', [2], '2017-12-31T23:59:58.000+00:00'],
            'sub week' => [[2018], 'subWeek', [], '2017-12-25T00:00:00.000+00:00'],
            'sub weeks' => [[2018], 'subWeeks', [2], '2017-12-18T00:00:00.000+00:00'],
            'sub year' => [[2018], 'subYear', [], '2017-01-01T00:00:00.000+00:00'],
            'sub years' => [[2018], 'subYears', [2], '2016-01-01T00:00:00.000+00:00'],
        ];
    }

    /**
     * @param int[] $parts
     * @param int[] $arguments
     */
    #[DataProvider('manipulateProvider')]
    public function testManipulate(array $parts, string $method, array $arguments, string $expected): void
    {
        $date1 = DateTime::createFromArray($parts);

        /** @var DateTime $date2 */
        $date2 = $date1->$method(...$arguments);

        $this->assertNotSame(
            $date1,
            $date2
        );
        $this->assertSame(
            $expected,
            $date2->toIsoString()
        );
    }
}
