<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use PHPUnit\Framework\Attributes\DataProvider;

trait StartsAfterTestTrait
{
    /**
     * @return array<string, array{int[], 'none'|'start', bool}>
     */
    public static function startsAfterProvider(): array
    {
        return [
            'equal' => [[2022, 1, 1], 'none', false],
            'after' => [[2022, 1, 2], 'none', false],
            'before' => [[2021, 12, 31], 'none', true],
            'excluded start' => [[2022, 1, 1], 'start', true],
            'included start' => [[2022, 1, 2], 'start', false],
        ];
    }

    /**
     * @param int[] $date
     * @param 'none'|'start' $excludeBoundaries
     */
    #[DataProvider('startsAfterProvider')]
    public function testStartsAfter(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->startsAfter(DateTime::createFromArray($date)));
    }

    /**
     * @param int[] $date
     * @param 'none'|'start' $excludeBoundaries
     */
    #[DataProvider('startsAfterProvider')]
    public function testStartsAfterDate(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            Date::createFromArray([2022, 1, 1]),
            Date::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->startsAfter(Date::createFromArray($date)));
    }
}
