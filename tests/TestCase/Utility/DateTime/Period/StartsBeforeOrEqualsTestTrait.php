<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use PHPUnit\Framework\Attributes\DataProvider;

trait StartsBeforeOrEqualsTestTrait
{
    /**
     * @return array<string, array{int[], 'none'|'start', bool}>
     */
    public static function startsBeforeOrEqualsProvider(): array
    {
        return [
            'equal' => [[2022, 1, 1], 'none', true],
            'after' => [[2022, 1, 2], 'none', true],
            'before' => [[2021, 12, 31], 'none', false],
            'excluded start' => [[2022, 1, 1], 'start', false],
            'included start' => [[2022, 1, 2], 'start', true],
        ];
    }

    /**
     * @param int[] $date
     * @param 'none'|'start' $excludeBoundaries
     */
    #[DataProvider('startsBeforeOrEqualsProvider')]
    public function testStartsBeforeOrEquals(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->startsBeforeOrEquals(DateTime::createFromArray($date)));
    }

    /**
     * @param int[] $date
     * @param 'none'|'start' $excludeBoundaries
     */
    #[DataProvider('startsBeforeOrEqualsProvider')]
    public function testStartsBeforeOrEqualsDate(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            Date::createFromArray([2022, 1, 1]),
            Date::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->startsBeforeOrEquals(Date::createFromArray($date)));
    }
}
