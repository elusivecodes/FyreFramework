<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;
use PHPUnit\Framework\Attributes\DataProvider;

trait AttributesGetTestTrait
{
    /**
     * @return array<string, array{int[], string, int|string}>
     */
    public static function attributeProvider(): array
    {
        return [
            'date' => [[2019, 1, 31], 'getDate', 31],
            'day' => [[2019, 1, 1], 'getDay', 2],
            'day Monday' => [[2018, 12, 31], 'getDay', 1],
            'day of year' => [[2019, 6, 1], 'getDayOfYear', 152],
            'day Sunday' => [[2018, 12, 30], 'getDay', 0],
            'hours' => [[2019, 1, 1, 6], 'getHours', 6],
            'hours 24 hour' => [[2019, 1, 1, 23], 'getHours', 23],
            'locale' => [[2019], 'getLocale', 'en'],
            'milliseconds' => [[2019, 1, 1, 0, 0, 0, 550], 'getMilliseconds', 550],
            'minutes' => [[2019, 1, 1, 0, 32], 'getMinutes', 32],
            'month' => [[2019, 6, 1], 'getMonth', 6],
            'quarter' => [[2019, 8, 1], 'getQuarter', 3],
            'seconds' => [[2019, 1, 1, 0, 0, 25], 'getSeconds', 25],
            'time' => [[2019, 1, 1], 'getTime', 1546300800000],
            'timestamp' => [[2019, 1, 1], 'getTimestamp', 1546300800],
            'week' => [[2019, 6, 1], 'getWeek', 22],
            'week day' => [[2019, 1, 1], 'getWeekDay', 3],
            'week day in month' => [[2019, 6, 1], 'getWeekDayInMonth', 1],
            'week day in month local' => [[2019, 6, 7], 'getWeekDayInMonth', 1],
            'week day Monday' => [[2018, 12, 31], 'getWeekDay', 2],
            'week day Sunday' => [[2018, 12, 30], 'getWeekDay', 1],
            'week of month' => [[2019, 6, 1], 'getWeekOfMonth', 1],
            'week of month local' => [[2019, 6, 3], 'getWeekOfMonth', 2],
            'week uses week year' => [[2019, 12, 30], 'getWeek', 1],
            'week year' => [[2019, 1, 1], 'getWeekYear', 2019],
            'week year Thursday' => [[2019, 12, 30], 'getWeekYear', 2020],
            'year' => [[2018], 'getYear', 2018],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function timeZoneProvider(): array
    {
        return [
            'name' => ['Australia/Brisbane', 'Australia/Brisbane'],
            'offset' => ['+10:00', '+10:00'],
            'offset without colon' => ['+1000', '+10:00'],
        ];
    }

    /**
     * @param int[] $parts
     */
    #[DataProvider('attributeProvider')]
    public function testGetAttribute(array $parts, string $method, int|string $expected): void
    {
        $date = DateTime::createFromArray($parts);

        $this->assertSame(
            $expected,
            $date->$method()
        );
    }

    #[DataProvider('timeZoneProvider')]
    public function testGetTimeZone(string $timeZone, string $expected): void
    {
        $this->assertSame(
            $expected,
            DateTime::now($timeZone)->getTimeZone()
        );
    }

    public function testGetTimeZoneOffset(): void
    {
        $this->assertSame(
            -600,
            DateTime::now('Australia/Brisbane')->getTimeZoneOffset()
        );
    }
}
