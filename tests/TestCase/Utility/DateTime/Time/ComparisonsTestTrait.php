<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Time;

use Fyre\Utility\DateTime\Time;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @phpstan-type IsAfterMethod 'isAfter'|'isAfterHour'|'isAfterMinute'|'isAfterSecond'
 * @phpstan-type IsBeforeMethod 'isBefore'|'isBeforeHour'|'isBeforeMinute'|'isBeforeSecond'
 * @phpstan-type IsBetweenMethod 'isBetween'|'isBetweenHour'|'isBetweenMinute'|'isBetweenSecond'
 * @phpstan-type IsSameMethod 'isSame'|'isSameHour'|'isSameMinute'|'isSameSecond'
 * @phpstan-type IsSameOrAfterMethod 'isSameOrAfter'|'isSameOrAfterHour'|'isSameOrAfterMinute'|'isSameOrAfterSecond'
 * @phpstan-type IsSameOrBeforeMethod 'isSameOrBefore'|'isSameOrBeforeHour'|'isSameOrBeforeMinute'|'isSameOrBeforeSecond'
 */
trait ComparisonsTestTrait
{
    /**
     * @return array<string, array{string, int[], int[], bool}>
     */
    public static function isAfterProvider(): array
    {
        return [
            'time after' => ['isAfter', [1, 1, 2], [1, 1, 1], true],
            'time before' => ['isAfter', [1, 1, 1], [1, 1, 2], false],
            'hour after' => ['isAfterHour', [2, 1], [1, 2], true],
            'hour before' => ['isAfterHour', [1, 2], [2, 1], false],
            'minute after' => ['isAfterMinute', [1, 2, 1], [1, 1, 2], true],
            'minute before' => ['isAfterMinute', [1, 1, 2], [1, 2, 1], false],
            'second after' => ['isAfterSecond', [1, 1, 2], [1, 1, 1], true],
            'second before' => ['isAfterSecond', [1, 1, 1], [1, 1, 2], false],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], bool}>
     */
    public static function isBeforeProvider(): array
    {
        return [
            'time after' => ['isBefore', [1, 1, 2], [1, 1, 1], false],
            'time before' => ['isBefore', [1, 1, 1], [1, 1, 2], true],
            'hour after' => ['isBeforeHour', [2, 1], [1, 2], false],
            'hour before' => ['isBeforeHour', [1, 2], [2, 1], true],
            'minute after' => ['isBeforeMinute', [1, 2, 1], [1, 1, 2], false],
            'minute before' => ['isBeforeMinute', [1, 1, 2], [1, 2, 1], true],
            'second after' => ['isBeforeSecond', [1, 1, 2], [1, 1, 1], false],
            'second before' => ['isBeforeSecond', [1, 1, 1], [1, 1, 2], true],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], int[], bool}>
     */
    public static function isBetweenProvider(): array
    {
        return [
            'time between' => ['isBetween', [1, 1, 2], [1, 1, 1], [1, 1, 3], true],
            'time boundary' => ['isBetween', [1, 1, 1], [1, 1, 1], [1, 1, 3], false],
            'hour between' => ['isBetweenHour', [2], [1], [3], true],
            'hour boundary' => ['isBetweenHour', [1], [1], [3], false],
            'minute between' => ['isBetweenMinute', [1, 2], [1, 1], [1, 3], true],
            'minute boundary' => ['isBetweenMinute', [1, 1], [1, 1], [1, 3], false],
            'second between' => ['isBetweenSecond', [1, 1, 2], [1, 1, 1], [1, 1, 3], true],
            'second boundary' => ['isBetweenSecond', [1, 1, 1], [1, 1, 1], [1, 1, 3], false],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], bool}>
     */
    public static function isSameOrAfterProvider(): array
    {
        return [
            'time before' => ['isSameOrAfter', [1, 1, 1], [1, 1, 2], false],
            'time same' => ['isSameOrAfter', [1, 1, 1], [1, 1, 1], true],
            'hour before' => ['isSameOrAfterHour', [1, 2], [2, 1], false],
            'hour same' => ['isSameOrAfterHour', [1, 2], [1, 1], true],
            'minute before' => ['isSameOrAfterMinute', [1, 1, 2], [1, 2, 1], false],
            'minute same' => ['isSameOrAfterMinute', [1, 1, 2], [1, 1, 1], true],
            'second before' => ['isSameOrAfterSecond', [1, 1, 1], [1, 1, 2], false],
            'second same' => ['isSameOrAfterSecond', [1, 1, 1, 2], [1, 1, 1, 1], true],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], bool}>
     */
    public static function isSameOrBeforeProvider(): array
    {
        return [
            'time after' => ['isSameOrBefore', [1, 1, 2], [1, 1, 1], false],
            'time same' => ['isSameOrBefore', [1, 1, 1], [1, 1, 1], true],
            'hour after' => ['isSameOrBeforeHour', [2, 1], [1, 2], false],
            'hour same' => ['isSameOrBeforeHour', [1, 2], [1, 1], true],
            'minute after' => ['isSameOrBeforeMinute', [1, 2, 1], [1, 1, 2], false],
            'minute same' => ['isSameOrBeforeMinute', [1, 1, 2], [1, 1, 1], true],
            'second after' => ['isSameOrBeforeSecond', [1, 1, 2], [1, 1, 1], false],
            'second same' => ['isSameOrBeforeSecond', [1, 1, 1, 2], [1, 1, 1, 1], true],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], bool}>
     */
    public static function isSameProvider(): array
    {
        return [
            'time different' => ['isSame', [1, 1, 1], [1, 1, 2], false],
            'time same' => ['isSame', [1, 1, 1], [1, 1, 1], true],
            'hour different' => ['isSameHour', [1, 2], [2, 1], false],
            'hour same' => ['isSameHour', [1, 2], [1, 1], true],
            'minute different' => ['isSameMinute', [1, 1, 2], [1, 2, 1], false],
            'minute same' => ['isSameMinute', [1, 1, 2], [1, 1, 1], true],
            'second different' => ['isSameSecond', [1, 1, 1], [1, 1, 2], false],
            'second same' => ['isSameSecond', [1, 1, 1, 2], [1, 1, 1, 1], true],
        ];
    }

    /**
     * @param IsAfterMethod $method
     * @param int[] $time1
     * @param int[] $time2
     */
    #[DataProvider('isAfterProvider')]
    public function testIsAfter(string $method, array $time1, array $time2, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Time::createFromArray($time1)->$method(Time::createFromArray($time2))
        );
    }

    /**
     * @param IsBeforeMethod $method
     * @param int[] $time1
     * @param int[] $time2
     */
    #[DataProvider('isBeforeProvider')]
    public function testIsBefore(string $method, array $time1, array $time2, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Time::createFromArray($time1)->$method(Time::createFromArray($time2))
        );
    }

    /**
     * @param IsBetweenMethod $method
     * @param int[] $time
     * @param int[] $start
     * @param int[] $end
     */
    #[DataProvider('isBetweenProvider')]
    public function testIsBetween(string $method, array $time, array $start, array $end, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Time::createFromArray($time)->$method(
                Time::createFromArray($start),
                Time::createFromArray($end)
            )
        );
    }

    /**
     * @param IsSameMethod $method
     * @param int[] $time1
     * @param int[] $time2
     */
    #[DataProvider('isSameProvider')]
    public function testIsSame(string $method, array $time1, array $time2, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Time::createFromArray($time1)->$method(Time::createFromArray($time2))
        );
    }

    /**
     * @param IsSameOrAfterMethod $method
     * @param int[] $time1
     * @param int[] $time2
     */
    #[DataProvider('isSameOrAfterProvider')]
    public function testIsSameOrAfter(string $method, array $time1, array $time2, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Time::createFromArray($time1)->$method(Time::createFromArray($time2))
        );
    }

    /**
     * @param IsSameOrBeforeMethod $method
     * @param int[] $time1
     * @param int[] $time2
     */
    #[DataProvider('isSameOrBeforeProvider')]
    public function testIsSameOrBefore(string $method, array $time1, array $time2, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Time::createFromArray($time1)->$method(Time::createFromArray($time2))
        );
    }
}
