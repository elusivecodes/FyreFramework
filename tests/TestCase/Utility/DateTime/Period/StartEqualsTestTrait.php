<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use PHPUnit\Framework\Attributes\DataProvider;

trait StartEqualsTestTrait
{
    /**
     * @return array<string, array{int[], 'none'|'start', bool}>
     */
    public static function startEqualsProvider(): array
    {
        return [
            'equal' => [[2022, 1, 1], 'none', true],
            'before' => [[2021, 12, 31], 'none', false],
            'after' => [[2022, 1, 2], 'none', false],
            'excluded start' => [[2022, 1, 1], 'start', false],
            'included start' => [[2022, 1, 2], 'start', true],
        ];
    }

    /**
     * @param int[] $date
     * @param 'none'|'start' $excludeBoundaries
     */
    #[DataProvider('startEqualsProvider')]
    public function testStartEquals(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->startEquals(DateTime::createFromArray($date)));
    }

    /**
     * @param int[] $date
     * @param 'none'|'start' $excludeBoundaries
     */
    #[DataProvider('startEqualsProvider')]
    public function testStartEqualsDate(array $date, string $excludeBoundaries, bool $expected): void
    {
        $period = new Period(
            Date::createFromArray([2022, 1, 1]),
            Date::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $this->assertSame($expected, $period->startEquals(Date::createFromArray($date)));
    }
}
