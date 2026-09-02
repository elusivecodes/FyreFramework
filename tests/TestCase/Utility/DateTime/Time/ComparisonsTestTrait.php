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
     * @return array<string, array{string, int[], int[]}>
     */
    public static function isAfterProvider(): array
    {
        return [
            'time' => ['isAfter', [1, 1, 2], [1, 1, 1]],
            'hour' => ['isAfterHour', [2, 1], [1, 2]],
            'minute' => ['isAfterMinute', [1, 2, 1], [1, 1, 2]],
            'second' => ['isAfterSecond', [1, 1, 2], [1, 1, 1]],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[]}>
     */
    public static function isBeforeProvider(): array
    {
        return [
            'time' => ['isBefore', [1, 1, 1], [1, 1, 2]],
            'hour' => ['isBeforeHour', [1, 2], [2, 1]],
            'minute' => ['isBeforeMinute', [1, 1, 2], [1, 2, 1]],
            'second' => ['isBeforeSecond', [1, 1, 1], [1, 1, 2]],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[], int[]}>
     */
    public static function isBetweenProvider(): array
    {
        return [
            'time' => ['isBetween', [1, 1, 2], [1, 1, 1], [1, 1, 3]],
            'hour' => ['isBetweenHour', [2], [1], [3]],
            'minute' => ['isBetweenMinute', [1, 2], [1, 1], [1, 3]],
            'second' => ['isBetweenSecond', [1, 1, 2], [1, 1, 1], [1, 1, 3]],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[]}>
     */
    public static function isSameOrAfterProvider(): array
    {
        return [
            'time' => ['isSameOrAfter', [1, 1, 1], [1, 1, 1]],
            'hour' => ['isSameOrAfterHour', [1, 2], [1, 1]],
            'minute' => ['isSameOrAfterMinute', [1, 1, 2], [1, 1, 1]],
            'second' => ['isSameOrAfterSecond', [1, 1, 1, 2], [1, 1, 1, 1]],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[]}>
     */
    public static function isSameOrBeforeProvider(): array
    {
        return [
            'time' => ['isSameOrBefore', [1, 1, 1], [1, 1, 1]],
            'hour' => ['isSameOrBeforeHour', [1, 2], [1, 1]],
            'minute' => ['isSameOrBeforeMinute', [1, 1, 2], [1, 1, 1]],
            'second' => ['isSameOrBeforeSecond', [1, 1, 1, 2], [1, 1, 1, 1]],
        ];
    }

    /**
     * @return array<string, array{string, int[], int[]}>
     */
    public static function isSameProvider(): array
    {
        return [
            'time' => ['isSame', [1, 1, 1], [1, 1, 1]],
            'hour' => ['isSameHour', [1, 2], [1, 1]],
            'minute' => ['isSameMinute', [1, 1, 2], [1, 1, 1]],
            'second' => ['isSameSecond', [1, 1, 1, 2], [1, 1, 1, 1]],
        ];
    }

    /**
     * @param IsAfterMethod $method
     * @param int[] $time1
     * @param int[] $time2
     */
    #[DataProvider('isAfterProvider')]
    public function testIsAfter(string $method, array $time1, array $time2): void
    {
        $this->assertTrue(
            Time::createFromArray($time1)->$method(Time::createFromArray($time2))
        );
    }

    /**
     * @param IsBeforeMethod $method
     * @param int[] $time1
     * @param int[] $time2
     */
    #[DataProvider('isBeforeProvider')]
    public function testIsBefore(string $method, array $time1, array $time2): void
    {
        $this->assertTrue(
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
    public function testIsBetween(string $method, array $time, array $start, array $end): void
    {
        $this->assertTrue(
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
    public function testIsSame(string $method, array $time1, array $time2): void
    {
        $this->assertTrue(
            Time::createFromArray($time1)->$method(Time::createFromArray($time2))
        );
    }

    /**
     * @param IsSameOrAfterMethod $method
     * @param int[] $time1
     * @param int[] $time2
     */
    #[DataProvider('isSameOrAfterProvider')]
    public function testIsSameOrAfter(string $method, array $time1, array $time2): void
    {
        $this->assertTrue(
            Time::createFromArray($time1)->$method(Time::createFromArray($time2))
        );
    }

    /**
     * @param IsSameOrBeforeMethod $method
     * @param int[] $time1
     * @param int[] $time2
     */
    #[DataProvider('isSameOrBeforeProvider')]
    public function testIsSameOrBefore(string $method, array $time1, array $time2): void
    {
        $this->assertTrue(
            Time::createFromArray($time1)->$method(Time::createFromArray($time2))
        );
    }
}
