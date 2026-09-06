<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Date;

use Fyre\Utility\DateTime\Date;
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
            'day of year' => [[2019, 6, 1], 'getDayOfYear', 152],
            'locale' => [[2019], 'getLocale', 'en'],
            'month' => [[2019, 6, 1], 'getMonth', 6],
            'quarter' => [[2019, 8, 1], 'getQuarter', 3],
            'time' => [[2019, 1, 1], 'getTime', 1546300800000],
            'timestamp' => [[2019, 1, 1], 'getTimestamp', 1546300800],
            'week' => [[2019, 6, 1], 'getWeek', 22],
            'week day' => [[2019, 1, 1], 'getWeekDay', 3],
            'week day in month' => [[2019, 6, 1], 'getWeekDayInMonth', 1],
            'week of month' => [[2019, 6, 1], 'getWeekOfMonth', 1],
            'week year' => [[2019, 1, 1], 'getWeekYear', 2019],
            'year' => [[2018], 'getYear', 2018],
        ];
    }

    /**
     * @param int[] $parts
     */
    #[DataProvider('attributeProvider')]
    public function testGetAttribute(array $parts, string $method, int|string $expected): void
    {
        $date = Date::createFromArray($parts);

        $this->assertSame(
            $expected,
            $date->$method()
        );
    }

    public function testGetTimeZone(): void
    {
        $this->assertSame(
            'UTC',
            Date::now('Australia/Brisbane')->getTimeZone()
        );
    }
}
