<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Date;

use Fyre\Utility\DateTime\Date;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @phpstan-type IsAfterMethod 'isAfter'|'isAfterDay'|'isAfterMonth'|'isAfterWeek'|'isAfterYear'
 * @phpstan-type IsBeforeMethod 'isBefore'|'isBeforeDay'|'isBeforeMonth'|'isBeforeWeek'|'isBeforeYear'
 * @phpstan-type IsBetweenMethod 'isBetween'|'isBetweenDay'|'isBetweenMonth'|'isBetweenWeek'|'isBetweenYear'
 * @phpstan-type IsSameMethod 'isSame'|'isSameDay'|'isSameMonth'|'isSameWeek'|'isSameYear'
 * @phpstan-type IsSameOrAfterMethod 'isSameOrAfter'|'isSameOrAfterDay'|'isSameOrAfterMonth'|'isSameOrAfterWeek'|'isSameOrAfterYear'
 * @phpstan-type IsSameOrBeforeMethod 'isSameOrBefore'|'isSameOrBeforeDay'|'isSameOrBeforeMonth'|'isSameOrBeforeWeek'|'isSameOrBeforeYear'
 */
trait ComparisonsTestTrait
{
    /**
     * @return array<string, array{string, int[], int[], bool}>
     */
    public static function isAfterProvider(): array
    {
        return [
            'date after' => ['isAfter', [2019, 1, 2], [2019, 1, 1], true],
            'date before' => ['isAfter', [2019, 1, 1], [2019, 1, 2], false],
            'day after' => ['isAfterDay', [2019, 1, 2], [2019, 1, 1], true],
            'day before' => ['isAfterDay', [2019, 1, 1], [2019, 1, 2], false],
            'month after' => ['isAfterMonth', [2019, 2, 1], [2019, 1, 2], true],
            'month before' => ['isAfterMonth', [2019, 1, 2], [2019, 2, 1], false],
            'week after' => ['isAfterWeek', [2019, 1, 9], [2019, 1, 1], true],
            'week before' => ['isAfterWeek', [2019, 1, 1], [2019, 1, 9], false],
            'year after' => ['isAfterYear', [2020, 1, 1], [2019, 2, 1], true],
            'year before' => ['isAfterYear', [2019, 2, 1], [2020, 1, 1], false],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], bool}>
     */
    public static function isBeforeProvider(): array
    {
        return [
            'date after' => ['isBefore', [2019, 1, 2], [2019, 1, 1], false],
            'date before' => ['isBefore', [2019, 1, 1], [2019, 1, 2], true],
            'day after' => ['isBeforeDay', [2019, 1, 2], [2019, 1, 1], false],
            'day before' => ['isBeforeDay', [2019, 1, 1], [2019, 1, 2], true],
            'month after' => ['isBeforeMonth', [2019, 2, 1], [2019, 1, 2], false],
            'month before' => ['isBeforeMonth', [2019, 1, 2], [2019, 2, 1], true],
            'week after' => ['isBeforeWeek', [2019, 1, 9], [2019, 1, 1], false],
            'week before' => ['isBeforeWeek', [2019, 1, 1], [2019, 1, 9], true],
            'year after' => ['isBeforeYear', [2020, 1, 1], [2019, 2, 1], false],
            'year before' => ['isBeforeYear', [2019, 2, 1], [2020, 1, 1], true],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], int[], bool}>
     */
    public static function isBetweenProvider(): array
    {
        return [
            'date between' => ['isBetween', [2019, 1, 2], [2019, 1, 1], [2019, 1, 3], true],
            'date boundary' => ['isBetween', [2019, 1, 1], [2019, 1, 1], [2019, 1, 3], false],
            'day between' => ['isBetweenDay', [2019, 1, 2], [2019, 1, 1], [2019, 1, 3], true],
            'day boundary' => ['isBetweenDay', [2019, 1, 1], [2019, 1, 1], [2019, 1, 3], false],
            'month between' => ['isBetweenMonth', [2019, 2, 1], [2019, 1, 2], [2019, 3, 1], true],
            'month boundary' => ['isBetweenMonth', [2019, 1, 2], [2019, 1, 1], [2019, 3, 1], false],
            'week between' => ['isBetweenWeek', [2019, 1, 9], [2019, 1, 1], [2019, 1, 17], true],
            'week boundary' => ['isBetweenWeek', [2019, 1, 1], [2019, 1, 1], [2019, 1, 17], false],
            'year between' => ['isBetweenYear', [2020, 1, 1], [2019, 2, 1], [2021, 1, 1], true],
            'year boundary' => ['isBetweenYear', [2019, 2, 1], [2019, 1, 1], [2021, 1, 1], false],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], bool}>
     */
    public static function isSameOrAfterProvider(): array
    {
        return [
            'date before' => ['isSameOrAfter', [2019, 1, 1], [2019, 1, 2], false],
            'date same' => ['isSameOrAfter', [2019, 1, 1], [2019, 1, 1], true],
            'day before' => ['isSameOrAfterDay', [2019, 1, 1], [2019, 1, 2], false],
            'day same' => ['isSameOrAfterDay', [2019, 1, 1], [2019, 1, 1], true],
            'month before' => ['isSameOrAfterMonth', [2019, 1, 2], [2019, 2, 1], false],
            'month same' => ['isSameOrAfterMonth', [2019, 1, 2], [2019, 1, 1], true],
            'week before' => ['isSameOrAfterWeek', [2019, 1, 1], [2019, 1, 9], false],
            'week same' => ['isSameOrAfterWeek', [2019, 1, 4], [2019, 1, 1], true],
            'year before' => ['isSameOrAfterYear', [2019, 2, 1], [2020, 1, 1], false],
            'year same' => ['isSameOrAfterYear', [2019, 2, 1], [2019, 1, 1], true],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], bool}>
     */
    public static function isSameOrBeforeProvider(): array
    {
        return [
            'date after' => ['isSameOrBefore', [2019, 1, 2], [2019, 1, 1], false],
            'date same' => ['isSameOrBefore', [2019, 1, 1], [2019, 1, 1], true],
            'day after' => ['isSameOrBeforeDay', [2019, 1, 2], [2019, 1, 1], false],
            'day same' => ['isSameOrBeforeDay', [2019, 1, 1], [2019, 1, 1], true],
            'month after' => ['isSameOrBeforeMonth', [2019, 2, 1], [2019, 1, 2], false],
            'month same' => ['isSameOrBeforeMonth', [2019, 1, 2], [2019, 1, 1], true],
            'week after' => ['isSameOrBeforeWeek', [2019, 1, 9], [2019, 1, 1], false],
            'week same' => ['isSameOrBeforeWeek', [2019, 1, 4], [2019, 1, 1], true],
            'year after' => ['isSameOrBeforeYear', [2020, 1, 1], [2019, 2, 1], false],
            'year same' => ['isSameOrBeforeYear', [2019, 2, 1], [2019, 1, 1], true],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], bool}>
     */
    public static function isSameProvider(): array
    {
        return [
            'date different' => ['isSame', [2019, 1, 1], [2019, 1, 2], false],
            'date same' => ['isSame', [2019, 1, 1], [2019, 1, 1], true],
            'day different' => ['isSameDay', [2019, 1, 1], [2019, 1, 2], false],
            'day same' => ['isSameDay', [2019, 1, 1], [2019, 1, 1], true],
            'month different' => ['isSameMonth', [2019, 1, 2], [2019, 2, 1], false],
            'month same' => ['isSameMonth', [2019, 1, 2], [2019, 1, 1], true],
            'week different' => ['isSameWeek', [2019, 1, 1], [2019, 1, 9], false],
            'week same' => ['isSameWeek', [2019, 1, 4], [2019, 1, 1], true],
            'year different' => ['isSameYear', [2019, 2, 1], [2020, 1, 1], false],
            'year same' => ['isSameYear', [2019, 2, 1], [2019, 1, 1], true],
        ];
    }

    /**
     * @param IsAfterMethod $method
     * @param int[] $date1
     * @param int[] $date2
     */
    #[DataProvider('isAfterProvider')]
    public function testIsAfter(string $method, array $date1, array $date2, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Date::createFromArray($date1)->$method(Date::createFromArray($date2))
        );
    }

    /**
     * @param IsBeforeMethod $method
     * @param int[] $date1
     * @param int[] $date2
     */
    #[DataProvider('isBeforeProvider')]
    public function testIsBefore(string $method, array $date1, array $date2, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Date::createFromArray($date1)->$method(Date::createFromArray($date2))
        );
    }

    /**
     * @param IsBetweenMethod $method
     * @param int[] $date
     * @param int[] $start
     * @param int[] $end
     */
    #[DataProvider('isBetweenProvider')]
    public function testIsBetween(string $method, array $date, array $start, array $end, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Date::createFromArray($date)->$method(
                Date::createFromArray($start),
                Date::createFromArray($end)
            )
        );
    }

    /**
     * @param IsSameMethod $method
     * @param int[] $date1
     * @param int[] $date2
     */
    #[DataProvider('isSameProvider')]
    public function testIsSame(string $method, array $date1, array $date2, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Date::createFromArray($date1)->$method(Date::createFromArray($date2))
        );
    }

    /**
     * @param IsSameOrAfterMethod $method
     * @param int[] $date1
     * @param int[] $date2
     */
    #[DataProvider('isSameOrAfterProvider')]
    public function testIsSameOrAfter(string $method, array $date1, array $date2, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Date::createFromArray($date1)->$method(Date::createFromArray($date2))
        );
    }

    /**
     * @param IsSameOrBeforeMethod $method
     * @param int[] $date1
     * @param int[] $date2
     */
    #[DataProvider('isSameOrBeforeProvider')]
    public function testIsSameOrBefore(string $method, array $date1, array $date2, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Date::createFromArray($date1)->$method(Date::createFromArray($date2))
        );
    }
}
