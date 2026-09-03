<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use PHPUnit\Framework\Attributes\DataProvider;

trait StartsAfterOrEqualsTestTrait
{
    /**
     * @return array<string, array{int[], 'none'|'start', bool}>
     */
    public static function startsAfterOrEqualsProvider(): array
    {
        return [
            'equal' => [[2022, 1, 1], 'none', true],
            'after' => [[2022, 1, 2], 'none', false],
            'before' => [[2021, 12, 31], 'none', true],
            'excluded start' => [[2022, 1, 1], 'start', true],
            'included start' => [[2022, 1, 2], 'start', true],
        ];
    }

    /**
     * @param int[] $date
     * @param 'none'|'start' $excludeBoundaries
     */
    #[DataProvider('startsAfterOrEqualsProvider')]
    public function testStartsAfterOrEquals(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->startsAfterOrEquals(DateTime::createFromArray($date)));
    }

    /**
     * @param int[] $date
     * @param 'none'|'start' $excludeBoundaries
     */
    #[DataProvider('startsAfterOrEqualsProvider')]
    public function testStartsAfterOrEqualsDate(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            Date::createFromArray([2022, 1, 1]),
            Date::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->startsAfterOrEquals(Date::createFromArray($date)));
    }
}
