<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;
use PHPUnit\Framework\Attributes\DataProvider;

trait UtilityTestTrait
{
    /**
     * @return array<string, array{int, string}>
     */
    public static function dayNameProvider(): array
    {
        return [
            'sunday' => [0, 'Sunday'],
            'monday' => [1, 'Monday'],
            'tuesday' => [2, 'Tuesday'],
            'wednesday' => [3, 'Wednesday'],
            'thursday' => [4, 'Thursday'],
            'friday' => [5, 'Friday'],
            'saturday' => [6, 'Saturday'],
        ];
    }

    /**
     * @return array<string, array{int, 'narrow'|'short', string}>
     */
    public static function dayNameWidthProvider(): array
    {
        return [
            'sunday narrow' => [0, 'narrow', 'S'],
            'monday narrow' => [1, 'narrow', 'M'],
            'tuesday narrow' => [2, 'narrow', 'T'],
            'wednesday narrow' => [3, 'narrow', 'W'],
            'thursday narrow' => [4, 'narrow', 'T'],
            'friday narrow' => [5, 'narrow', 'F'],
            'saturday narrow' => [6, 'narrow', 'S'],
            'sunday short' => [0, 'short', 'Sun'],
            'monday short' => [1, 'short', 'Mon'],
            'tuesday short' => [2, 'short', 'Tue'],
            'wednesday short' => [3, 'short', 'Wed'],
            'thursday short' => [4, 'short', 'Thu'],
            'friday short' => [5, 'short', 'Fri'],
            'saturday short' => [6, 'short', 'Sat'],
        ];
    }

    /**
     * @return array<string, array{int, int, int}>
     */
    public static function daysInMonthProvider(): array
    {
        return [
            '2018-01' => [2018, 1, 31],
            '2018-02' => [2018, 2, 28],
            '2018-03' => [2018, 3, 31],
            '2018-04' => [2018, 4, 30],
            '2018-05' => [2018, 5, 31],
            '2018-06' => [2018, 6, 30],
            '2018-07' => [2018, 7, 31],
            '2018-08' => [2018, 8, 31],
            '2018-09' => [2018, 9, 30],
            '2018-10' => [2018, 10, 31],
            '2018-11' => [2018, 11, 30],
            '2018-12' => [2018, 12, 31],
            'leap February' => [2020, 2, 29],
        ];
    }

    /**
     * @return array<string, array{int, 'narrow'|'short', string}>
     */
    public static function eraWidthProvider(): array
    {
        return [
            'narrow' => [2018, 'narrow', 'A'],
            'narrow bc' => [-5, 'narrow', 'B'],
            'short' => [2018, 'short', 'AD'],
            'short bc' => [-5, 'short', 'BC'],
        ];
    }

    /**
     * @return array<string, array{int, string}>
     */
    public static function monthNameProvider(): array
    {
        return [
            'january' => [1, 'January'],
            'february' => [2, 'February'],
            'march' => [3, 'March'],
            'april' => [4, 'April'],
            'may' => [5, 'May'],
            'june' => [6, 'June'],
            'july' => [7, 'July'],
            'august' => [8, 'August'],
            'september' => [9, 'September'],
            'october' => [10, 'October'],
            'november' => [11, 'November'],
            'december' => [12, 'December'],
        ];
    }

    /**
     * @return array<string, array{int, 'narrow'|'short', string}>
     */
    public static function monthNameWidthProvider(): array
    {
        return [
            'january narrow' => [1, 'narrow', 'J'],
            'february narrow' => [2, 'narrow', 'F'],
            'march narrow' => [3, 'narrow', 'M'],
            'april narrow' => [4, 'narrow', 'A'],
            'may narrow' => [5, 'narrow', 'M'],
            'june narrow' => [6, 'narrow', 'J'],
            'july narrow' => [7, 'narrow', 'J'],
            'august narrow' => [8, 'narrow', 'A'],
            'september narrow' => [9, 'narrow', 'S'],
            'october narrow' => [10, 'narrow', 'O'],
            'november narrow' => [11, 'narrow', 'N'],
            'december narrow' => [12, 'narrow', 'D'],
            'january short' => [1, 'short', 'Jan'],
            'february short' => [2, 'short', 'Feb'],
            'march short' => [3, 'short', 'Mar'],
            'april short' => [4, 'short', 'Apr'],
            'may short' => [5, 'short', 'May'],
            'june short' => [6, 'short', 'Jun'],
            'july short' => [7, 'short', 'Jul'],
            'august short' => [8, 'short', 'Aug'],
            'september short' => [9, 'short', 'Sep'],
            'october short' => [10, 'short', 'Oct'],
            'november short' => [11, 'short', 'Nov'],
            'december short' => [12, 'short', 'Dec'],
        ];
    }

    #[DataProvider('dayNameProvider')]
    public function testDayName(int $day, string $expected): void
    {
        $this->assertSame(
            $expected,
            DateTime::createFromArray([2019, 1, 1])->withDay($day)->dayName()
        );
    }

    /**
     * @param 'narrow'|'short' $width
     */
    #[DataProvider('dayNameWidthProvider')]
    public function testDayNameWidth(int $day, string $width, string $expected): void
    {
        $this->assertSame(
            $expected,
            DateTime::createFromArray([2019, 1, 1])->withDay($day)->dayName($width)
        );
    }

    public function testDayPeriod(): void
    {
        $this->assertSame(
            'AM',
            DateTime::createFromArray([2019, 1, 1, 0])
                ->dayPeriod(),
        );
    }

    public function testDayPeriodPm(): void
    {
        $this->assertSame(
            'PM',
            DateTime::createFromArray([2019, 1, 1, 12])
                ->dayPeriod(),
        );
    }

    public function testDayPeriodShort(): void
    {
        $this->assertSame(
            'AM',
            DateTime::createFromArray([2019, 1, 1, 0])
                ->dayPeriod('short'),
        );
    }

    public function testDayPeriodShortPm(): void
    {
        $this->assertSame(
            'PM',
            DateTime::createFromArray([2019, 1, 1, 12])
                ->dayPeriod('short'),
        );
    }

    #[DataProvider('daysInMonthProvider')]
    public function testDaysInMonth(int $year, int $month, int $expected): void
    {
        $this->assertSame(
            $expected,
            DateTime::createFromArray([$year, $month, 1])->daysInMonth()
        );
    }

    public function testDaysInYear(): void
    {
        $this->assertSame(
            365,
            DateTime::createFromArray([2018, 1, 1])
                ->daysInYear(),
        );
    }

    public function testDaysInYearLeapYear(): void
    {
        $this->assertSame(
            366,
            DateTime::createFromArray([2020, 1, 1])
                ->daysInYear(),
        );
    }

    public function testEra(): void
    {
        $this->assertSame(
            'Anno Domini',
            DateTime::createFromArray([2018])
                ->era(),
        );
    }

    public function testEraBc(): void
    {
        $this->assertSame(
            'Before Christ',
            DateTime::createFromArray([-5])
                ->era(),
        );
    }

    /**
     * @param 'narrow'|'short' $width
     */
    #[DataProvider('eraWidthProvider')]
    public function testEraWidth(int $year, string $width, string $expected): void
    {
        $this->assertSame(
            $expected,
            DateTime::createFromArray([$year])->era($width)
        );
    }

    public function testIsDst(): void
    {
        $this->assertFalse(
            DateTime::createFromArray([2018, 1, 1])
                ->isDst(),
        );
    }

    public function testIsDstDst(): void
    {
        $this->assertTrue(
            DateTime::createFromArray([2018, 6, 1], 'America/New_York')
                ->isDst(),
        );
    }

    public function testIsLeapYear(): void
    {
        $this->assertFalse(
            DateTime::createFromArray([2019])
                ->isLeapYear(),
        );
    }

    public function testIsLeapYearLeapYear(): void
    {
        $this->assertTrue(
            DateTime::createFromArray([2020])
                ->isLeapYear(),
        );
    }

    #[DataProvider('monthNameProvider')]
    public function testMonthName(int $month, string $expected): void
    {
        $this->assertSame(
            $expected,
            DateTime::createFromArray([2019, $month, 1])->monthName()
        );
    }

    /**
     * @param 'narrow'|'short' $width
     */
    #[DataProvider('monthNameWidthProvider')]
    public function testMonthNameWidth(int $month, string $width, string $expected): void
    {
        $this->assertSame(
            $expected,
            DateTime::createFromArray([2019, $month, 1])->monthName($width)
        );
    }

    public function testTimeZoneName(): void
    {
        $this->assertSame(
            'Australian Eastern Standard Time',
            DateTime::createFromArray([2018], 'Australia/Brisbane')
                ->timeZoneName(),
        );
    }

    public function testTimeZoneNameOffset(): void
    {
        $this->assertSame(
            'GMT+10:00',
            DateTime::createFromArray([2018], '+10:00')
                ->timeZoneName(),
        );
    }

    public function testTimeZoneNameShort(): void
    {
        $this->assertSame(
            'GMT+10',
            DateTime::createFromArray([2018], 'Australia/Brisbane')
                ->timeZoneName('short'),
        );
    }

    public function testTimeZoneNameShortOffset(): void
    {
        $this->assertSame(
            'GMT+10',
            DateTime::createFromArray([2018], '+10:00')
                ->timeZoneName('short'),
        );
    }

    public function testWeeksInYear(): void
    {
        $this->assertSame(
            52,
            DateTime::createFromArray([2018, 1, 1])
                ->weeksInYear(),
        );
    }

    public function testWeeksInYearLocal(): void
    {
        $this->assertSame(
            53,
            DateTime::createFromArray([2016, 1, 1])
                ->weeksInYear(),
        );
    }
}
