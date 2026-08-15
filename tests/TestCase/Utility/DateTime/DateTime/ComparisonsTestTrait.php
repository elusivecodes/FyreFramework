<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @phpstan-type IsAfterMethod 'isAfter'|'isAfterDay'|'isAfterHour'|'isAfterMinute'|'isAfterMonth'|'isAfterSecond'|'isAfterYear'
 * @phpstan-type IsBeforeMethod 'isBefore'|'isBeforeDay'|'isBeforeHour'|'isBeforeMinute'|'isBeforeMonth'|'isBeforeSecond'|'isBeforeYear'
 * @phpstan-type IsBetweenMethod 'isBetween'|'isBetweenDay'|'isBetweenHour'|'isBetweenMinute'|'isBetweenMonth'|'isBetweenSecond'|'isBetweenYear'
 * @phpstan-type IsSameMethod 'isSame'|'isSameDay'|'isSameHour'|'isSameMinute'|'isSameMonth'|'isSameSecond'|'isSameYear'
 * @phpstan-type IsSameOrAfterMethod 'isSameOrAfter'|'isSameOrAfterDay'|'isSameOrAfterHour'|'isSameOrAfterMinute'|'isSameOrAfterMonth'|'isSameOrAfterSecond'|'isSameOrAfterYear'
 * @phpstan-type IsSameOrBeforeMethod 'isSameOrBefore'|'isSameOrBeforeDay'|'isSameOrBeforeHour'|'isSameOrBeforeMinute'|'isSameOrBeforeMonth'|'isSameOrBeforeSecond'|'isSameOrBeforeYear'
 */
trait ComparisonsTestTrait
{
    /**
     * @return array<string, array{string, int[], int[], bool}>
     */
    public static function isAfterProvider(): array
    {
        return [
            'after' => ['isAfter', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 2, 2], false],
            'before' => ['isAfter', [2018, 1, 1, 1, 2, 2], [2018, 1, 1, 1, 1, 1], true],
            'day after' => ['isAfterDay', [2018, 1, 1, 1], [2018, 1, 2, 2], false],
            'day before' => ['isAfterDay', [2018, 1, 2, 2], [2018, 1, 1, 1], true],
            'hour after' => ['isAfterHour', [2018, 1, 1, 1, 1], [2018, 1, 1, 2, 2], false],
            'hour before' => ['isAfterHour', [2018, 1, 1, 2, 2], [2018, 1, 1, 1, 1], true],
            'minute after' => ['isAfterMinute', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 2, 2], false],
            'minute before' => ['isAfterMinute', [2018, 1, 1, 1, 2, 2], [2018, 1, 1, 1, 1, 1], true],
            'month after' => ['isAfterMonth', [2018, 1, 1], [2018, 2, 2], false],
            'month before' => ['isAfterMonth', [2018, 2, 2], [2018, 1, 1], true],
            'second after' => ['isAfterSecond', [2018, 1, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 1, 2, 2], false],
            'second before' => ['isAfterSecond', [2018, 1, 1, 1, 1, 2, 2], [2018, 1, 1, 1, 1, 1, 1], true],
            'year after' => ['isAfterYear', [2018, 1], [2019, 2], false],
            'year before' => ['isAfterYear', [2019, 2], [2018, 1], true],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], bool}>
     */
    public static function isBeforeProvider(): array
    {
        return [
            'after' => ['isBefore', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 2, 2], true],
            'before' => ['isBefore', [2018, 1, 1, 1, 2, 2], [2018, 1, 1, 1, 1, 1], false],
            'day after' => ['isBeforeDay', [2018, 1, 1, 1], [2018, 1, 2, 2], true],
            'day before' => ['isBeforeDay', [2018, 1, 2, 2], [2018, 1, 1, 1], false],
            'hour after' => ['isBeforeHour', [2018, 1, 1, 1, 1], [2018, 1, 1, 2, 2], true],
            'hour before' => ['isBeforeHour', [2018, 1, 1, 2, 2], [2018, 1, 1, 1, 1], false],
            'minute after' => ['isBeforeMinute', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 2, 2], true],
            'minute before' => ['isBeforeMinute', [2018, 1, 1, 1, 2, 2], [2018, 1, 1, 1, 1, 1], false],
            'month after' => ['isBeforeMonth', [2018, 1, 1], [2018, 2, 2], true],
            'month before' => ['isBeforeMonth', [2018, 2, 2], [2018, 1, 1], false],
            'second after' => ['isBeforeSecond', [2018, 1, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 1, 2, 2], true],
            'second before' => ['isBeforeSecond', [2018, 1, 1, 1, 1, 2, 2], [2018, 1, 1, 1, 1, 1, 1], false],
            'year after' => ['isBeforeYear', [2018, 1], [2019, 2], true],
            'year before' => ['isBeforeYear', [2019, 2], [2018, 1], false],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], int[], bool}>
     */
    public static function isBetweenProvider(): array
    {
        return [
            'after' => ['isBetween', [2018, 1, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 1, 1, 4], false],
            'before' => ['isBetween', [2018, 1, 1, 1, 1, 1, 5], [2018, 1, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 1, 1, 4], false],
            'between' => ['isBetween', [2018, 1, 1, 1, 1, 1, 3], [2018, 1, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 1, 1, 4], true],
            'day after' => ['isBetweenDay', [2018, 1, 1], [2018, 1, 2], [2018, 1, 4], false],
            'day before' => ['isBetweenDay', [2018, 1, 5], [2018, 1, 2], [2018, 1, 4], false],
            'day between' => ['isBetweenDay', [2018, 1, 3], [2018, 1, 2], [2018, 1, 4], true],
            'hour after' => ['isBetweenHour', [2018, 1, 1, 1], [2018, 1, 1, 2], [2018, 1, 1, 4], false],
            'hour before' => ['isBetweenHour', [2018, 1, 1, 5], [2018, 1, 1, 2], [2018, 1, 1, 4], false],
            'hour between' => ['isBetweenHour', [2018, 1, 1, 3], [2018, 1, 1, 2], [2018, 1, 1, 4], true],
            'minute after' => ['isBetweenMinute', [2018, 1, 1, 1, 1], [2018, 1, 1, 1, 2], [2018, 1, 1, 1, 4], false],
            'minute before' => ['isBetweenMinute', [2018, 1, 1, 1, 5], [2018, 1, 1, 1, 2], [2018, 1, 1, 1, 4], false],
            'minute between' => ['isBetweenMinute', [2018, 1, 1, 1, 3], [2018, 1, 1, 1, 2], [2018, 1, 1, 1, 4], true],
            'month after' => ['isBetweenMonth', [2018, 1], [2018, 2], [2018, 4], false],
            'month before' => ['isBetweenMonth', [2018, 5], [2018, 2], [2018, 4], false],
            'month between' => ['isBetweenMonth', [2018, 3], [2018, 2], [2018, 4], true],
            'second after' => ['isBetweenSecond', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 1, 4], false],
            'second before' => ['isBetweenSecond', [2018, 1, 1, 1, 1, 5], [2018, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 1, 4], false],
            'second between' => ['isBetweenSecond', [2018, 1, 1, 1, 1, 3], [2018, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 1, 4], true],
            'year after' => ['isBetweenYear', [2017], [2018], [2020], false],
            'year before' => ['isBetweenYear', [2021], [2018], [2020], false],
            'year between' => ['isBetweenYear', [2019], [2018], [2020], true],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], bool}>
     */
    public static function isSameOrAfterProvider(): array
    {
        return [
            'after' => ['isSameOrAfter', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 1, 2], false],
            'before' => ['isSameOrAfter', [2018, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 1, 1], true],
            'day after' => ['isSameOrAfterDay', [2018, 1, 1, 2], [2018, 1, 2, 1], false],
            'day before' => ['isSameOrAfterDay', [2018, 1, 2, 2], [2018, 1, 1, 1], true],
            'day same' => ['isSameOrAfterDay', [2018, 1, 1, 2], [2018, 1, 1, 1], true],
            'hour after' => ['isSameOrAfterHour', [2018, 1, 1, 1, 2], [2018, 1, 1, 2, 1], false],
            'hour before' => ['isSameOrAfterHour', [2018, 1, 1, 2, 2], [2018, 1, 1, 1, 1], true],
            'hour same' => ['isSameOrAfterHour', [2018, 1, 1, 1, 2], [2018, 1, 1, 1, 1], true],
            'minute after' => ['isSameOrAfterMinute', [2018, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 2, 1], false],
            'minute before' => ['isSameOrAfterMinute', [2018, 1, 1, 1, 2, 2], [2018, 1, 1, 1, 1, 1], true],
            'minute same' => ['isSameOrAfterMinute', [2018, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 1, 1], true],
            'month after' => ['isSameOrAfterMonth', [2018, 1, 2], [2018, 2, 1], false],
            'month before' => ['isSameOrAfterMonth', [2018, 2, 2], [2018, 1, 1], true],
            'month same' => ['isSameOrAfterMonth', [2018, 1, 2], [2018, 1, 1], true],
            'same' => ['isSameOrAfter', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 1, 1], true],
            'second after' => ['isSameOrAfterSecond', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 1, 2], false],
            'second before' => ['isSameOrAfterSecond', [2018, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 1, 1], true],
            'second same' => ['isSameOrAfterSecond', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 1, 1], true],
            'year after' => ['isSameOrAfterYear', [2018, 2], [2019, 1], false],
            'year before' => ['isSameOrAfterYear', [2018, 2], [2017, 1], true],
            'year same' => ['isSameOrAfterYear', [2018, 2], [2018, 1], true],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], bool}>
     */
    public static function isSameOrBeforeProvider(): array
    {
        return [
            'after' => ['isSameOrBefore', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 1, 2], true],
            'before' => ['isSameOrBefore', [2018, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 1, 1], false],
            'day after' => ['isSameOrBeforeDay', [2018, 1, 1, 2], [2018, 1, 2, 1], true],
            'day before' => ['isSameOrBeforeDay', [2018, 1, 2, 2], [2018, 1, 1, 1], false],
            'day same' => ['isSameOrBeforeDay', [2018, 1, 1, 2], [2018, 1, 1, 1], true],
            'hour after' => ['isSameOrBeforeHour', [2018, 1, 1, 1, 2], [2018, 1, 1, 2, 1], true],
            'hour before' => ['isSameOrBeforeHour', [2018, 1, 1, 2, 2], [2018, 1, 1, 1, 1], false],
            'hour same' => ['isSameOrBeforeHour', [2018, 1, 1, 1, 2], [2018, 1, 1, 1, 1], true],
            'minute after' => ['isSameOrBeforeMinute', [2018, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 2, 1], true],
            'minute before' => ['isSameOrBeforeMinute', [2018, 1, 1, 1, 2, 2], [2018, 1, 1, 1, 1, 1], false],
            'minute same' => ['isSameOrBeforeMinute', [2018, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 1, 1], true],
            'month after' => ['isSameOrBeforeMonth', [2018, 1, 2], [2018, 2, 1], true],
            'month before' => ['isSameOrBeforeMonth', [2018, 2, 2], [2018, 1, 1], false],
            'month same' => ['isSameOrBeforeMonth', [2018, 1, 2], [2018, 1, 1], true],
            'same' => ['isSameOrBefore', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 1, 1], true],
            'second after' => ['isSameOrBeforeSecond', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 1, 2], true],
            'second before' => ['isSameOrBeforeSecond', [2018, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 1, 1], false],
            'second same' => ['isSameOrBeforeSecond', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 1, 1], true],
            'year after' => ['isSameOrBeforeYear', [2018, 2], [2019, 1], true],
            'year before' => ['isSameOrBeforeYear', [2018, 2], [2017, 1], false],
            'year same' => ['isSameOrBeforeYear', [2018, 2], [2018, 1], true],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], bool}>
     */
    public static function isSameProvider(): array
    {
        return [
            'after' => ['isSame', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 1, 2], false],
            'before' => ['isSame', [2018, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 1, 1], false],
            'day after' => ['isSameDay', [2018, 1, 1, 2], [2018, 1, 2, 1], false],
            'day before' => ['isSameDay', [2018, 1, 2, 2], [2018, 1, 1, 1], false],
            'day same' => ['isSameDay', [2018, 1, 1, 2], [2018, 1, 1, 1], true],
            'hour after' => ['isSameHour', [2018, 1, 1, 1, 2], [2018, 1, 1, 2, 1], false],
            'hour before' => ['isSameHour', [2018, 1, 1, 2, 2], [2018, 1, 1, 1, 1], false],
            'hour same' => ['isSameHour', [2018, 1, 1, 1, 2], [2018, 1, 1, 1, 1], true],
            'minute after' => ['isSameMinute', [2018, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 2, 1], false],
            'minute before' => ['isSameMinute', [2018, 1, 1, 1, 2, 2], [2018, 1, 1, 1, 1, 1], false],
            'minute same' => ['isSameMinute', [2018, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 1, 1], true],
            'month after' => ['isSameMonth', [2018, 1, 2], [2018, 2, 1], false],
            'month before' => ['isSameMonth', [2018, 2, 2], [2018, 1, 1], false],
            'month same' => ['isSameMonth', [2018, 1, 2], [2018, 1, 1], true],
            'same' => ['isSame', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 1, 1], true],
            'second after' => ['isSameSecond', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 1, 2], false],
            'second before' => ['isSameSecond', [2018, 1, 1, 1, 1, 2], [2018, 1, 1, 1, 1, 1], false],
            'second same' => ['isSameSecond', [2018, 1, 1, 1, 1, 1], [2018, 1, 1, 1, 1, 1], true],
            'year after' => ['isSameYear', [2018, 2], [2019, 1], false],
            'year before' => ['isSameYear', [2018, 2], [2017, 1], false],
            'year same' => ['isSameYear', [2018, 2], [2018, 1], true],
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
        $first = DateTime::createFromArray($date1);
        $second = DateTime::createFromArray($date2);

        $this->assertSame(
            $expected,
            $first->$method($second)
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
        $first = DateTime::createFromArray($date1);
        $second = DateTime::createFromArray($date2);

        $this->assertSame(
            $expected,
            $first->$method($second)
        );
    }

    /**
     * @param IsBetweenMethod $method
     * @param int[] $date1
     * @param int[] $date2
     * @param int[] $date3
     */
    #[DataProvider('isBetweenProvider')]
    public function testIsBetween(string $method, array $date1, array $date2, array $date3, bool $expected): void
    {
        $first = DateTime::createFromArray($date1);
        $second = DateTime::createFromArray($date2);
        $third = DateTime::createFromArray($date3);

        $this->assertSame(
            $expected,
            $first->$method($second, $third)
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
        $first = DateTime::createFromArray($date1);
        $second = DateTime::createFromArray($date2);

        $this->assertSame(
            $expected,
            $first->$method($second)
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
        $first = DateTime::createFromArray($date1);
        $second = DateTime::createFromArray($date2);

        $this->assertSame(
            $expected,
            $first->$method($second)
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
        $first = DateTime::createFromArray($date1);
        $second = DateTime::createFromArray($date2);

        $this->assertSame(
            $expected,
            $first->$method($second)
        );
    }
}
