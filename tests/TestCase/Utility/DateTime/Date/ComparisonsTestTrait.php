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
     * @return array<string, array{string, int[], int[]}>
     */
    public static function isAfterProvider(): array
    {
        return [
            'date' => ['isAfter', [2019, 1, 2], [2019, 1, 1]],
            'day' => ['isAfterDay', [2019, 1, 2], [2019, 1, 1]],
            'month' => ['isAfterMonth', [2019, 2, 1], [2019, 1, 2]],
            'week' => ['isAfterWeek', [2019, 1, 9], [2019, 1, 1]],
            'year' => ['isAfterYear', [2020, 1, 1], [2019, 2, 1]],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[]}>
     */
    public static function isBeforeProvider(): array
    {
        return [
            'date' => ['isBefore', [2019, 1, 1], [2019, 1, 2]],
            'day' => ['isBeforeDay', [2019, 1, 1], [2019, 1, 2]],
            'month' => ['isBeforeMonth', [2019, 1, 2], [2019, 2, 1]],
            'week' => ['isBeforeWeek', [2019, 1, 1], [2019, 1, 9]],
            'year' => ['isBeforeYear', [2019, 2, 1], [2020, 1, 1]],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], int[]}>
     */
    public static function isBetweenProvider(): array
    {
        return [
            'date' => ['isBetween', [2019, 1, 2], [2019, 1, 1], [2019, 1, 3]],
            'day' => ['isBetweenDay', [2019, 1, 2], [2019, 1, 1], [2019, 1, 3]],
            'month' => ['isBetweenMonth', [2019, 2, 1], [2019, 1, 2], [2019, 3, 1]],
            'week' => ['isBetweenWeek', [2019, 1, 9], [2019, 1, 1], [2019, 1, 17]],
            'year' => ['isBetweenYear', [2020, 1, 1], [2019, 2, 1], [2021, 1, 1]],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[]}>
     */
    public static function isSameOrAfterProvider(): array
    {
        return [
            'date' => ['isSameOrAfter', [2019, 1, 1], [2019, 1, 1]],
            'day' => ['isSameOrAfterDay', [2019, 1, 1], [2019, 1, 1]],
            'month' => ['isSameOrAfterMonth', [2019, 1, 2], [2019, 1, 1]],
            'week' => ['isSameOrAfterWeek', [2019, 1, 4], [2019, 1, 1]],
            'year' => ['isSameOrAfterYear', [2019, 2, 1], [2019, 1, 1]],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[]}>
     */
    public static function isSameOrBeforeProvider(): array
    {
        return [
            'date' => ['isSameOrBefore', [2019, 1, 1], [2019, 1, 1]],
            'day' => ['isSameOrBeforeDay', [2019, 1, 1], [2019, 1, 1]],
            'month' => ['isSameOrBeforeMonth', [2019, 1, 2], [2019, 1, 1]],
            'week' => ['isSameOrBeforeWeek', [2019, 1, 4], [2019, 1, 1]],
            'year' => ['isSameOrBeforeYear', [2019, 2, 1], [2019, 1, 1]],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[]}>
     */
    public static function isSameProvider(): array
    {
        return [
            'date' => ['isSame', [2019, 1, 1], [2019, 1, 1]],
            'day' => ['isSameDay', [2019, 1, 1], [2019, 1, 1]],
            'month' => ['isSameMonth', [2019, 1, 2], [2019, 1, 1]],
            'week' => ['isSameWeek', [2019, 1, 4], [2019, 1, 1]],
            'year' => ['isSameYear', [2019, 2, 1], [2019, 1, 1]],
        ];
    }

    /**
     * @param IsAfterMethod $method
     * @param int[] $date1
     * @param int[] $date2
     */
    #[DataProvider('isAfterProvider')]
    public function testIsAfter(string $method, array $date1, array $date2): void
    {
        $this->assertTrue(
            Date::createFromArray($date1)->$method(Date::createFromArray($date2))
        );
    }

    /**
     * @param IsBeforeMethod $method
     * @param int[] $date1
     * @param int[] $date2
     */
    #[DataProvider('isBeforeProvider')]
    public function testIsBefore(string $method, array $date1, array $date2): void
    {
        $this->assertTrue(
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
    public function testIsBetween(string $method, array $date, array $start, array $end): void
    {
        $this->assertTrue(
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
    public function testIsSame(string $method, array $date1, array $date2): void
    {
        $this->assertTrue(
            Date::createFromArray($date1)->$method(Date::createFromArray($date2))
        );
    }

    /**
     * @param IsSameOrAfterMethod $method
     * @param int[] $date1
     * @param int[] $date2
     */
    #[DataProvider('isSameOrAfterProvider')]
    public function testIsSameOrAfter(string $method, array $date1, array $date2): void
    {
        $this->assertTrue(
            Date::createFromArray($date1)->$method(Date::createFromArray($date2))
        );
    }

    /**
     * @param IsSameOrBeforeMethod $method
     * @param int[] $date1
     * @param int[] $date2
     */
    #[DataProvider('isSameOrBeforeProvider')]
    public function testIsSameOrBefore(string $method, array $date1, array $date2): void
    {
        $this->assertTrue(
            Date::createFromArray($date1)->$method(Date::createFromArray($date2))
        );
    }
}
