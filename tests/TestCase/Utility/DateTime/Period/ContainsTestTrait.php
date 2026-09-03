<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;

trait ContainsTestTrait
{
    /**
     * @return array<string, array{int[], int[], 'end'|'none'|'start', int[], int[], 'end'|'none'|'start', bool}>
     */
    public static function containsProvider(): array
    {
        return [
            'contained' => [
                [2022, 1, 1],
                [2022, 1, 15],
                'none',
                [2022, 1, 2],
                [2022, 1, 14],
                'none',
                true,
            ],
            'end after' => [
                [2022, 1, 1],
                [2022, 1, 14],
                'none',
                [2022, 1, 2],
                [2022, 1, 15],
                'none',
                false,
            ],
            'end after excluded' => [
                [2022, 1, 1],
                [2022, 1, 14],
                'none',
                [2022, 1, 2],
                [2022, 1, 15],
                'end',
                true,
            ],
            'no overlap' => [
                [2022, 1, 1],
                [2022, 1, 10],
                'none',
                [2022, 1, 15],
                [2022, 1, 20],
                'none',
                false,
            ],
            'start before' => [
                [2022, 1, 2],
                [2022, 1, 15],
                'none',
                [2022, 1, 1],
                [2022, 1, 14],
                'none',
                false,
            ],
            'start before excluded' => [
                [2022, 1, 2],
                [2022, 1, 15],
                'none',
                [2022, 1, 1],
                [2022, 1, 14],
                'start',
                true,
            ],
        ];
    }

    /**
     * @param int[] $start1
     * @param int[] $end1
     * @param 'end'|'none'|'start' $excludeBoundaries1
     * @param int[] $start2
     * @param int[] $end2
     * @param 'end'|'none'|'start' $excludeBoundaries2
     */
    #[DataProvider('containsProvider')]
    public function testContains(array $start1, array $end1, string $excludeBoundaries1, array $start2, array $end2, string $excludeBoundaries2, bool $expected): void
    {
        $period1 = new Period(
            DateTime::createFromArray($start1),
            DateTime::createFromArray($end1),
            excludeBoundaries: $excludeBoundaries1
        );
        $period2 = new Period(
            DateTime::createFromArray($start2),
            DateTime::createFromArray($end2),
            excludeBoundaries: $excludeBoundaries2
        );

        $this->assertSame($expected, $period1->contains($period2));
    }

    /**
     * @param int[] $start1
     * @param int[] $end1
     * @param 'end'|'none'|'start' $excludeBoundaries1
     * @param int[] $start2
     * @param int[] $end2
     * @param 'end'|'none'|'start' $excludeBoundaries2
     */
    #[DataProvider('containsProvider')]
    public function testContainsDate(array $start1, array $end1, string $excludeBoundaries1, array $start2, array $end2, string $excludeBoundaries2, bool $expected): void
    {
        $period1 = new Period(
            Date::createFromArray($start1),
            Date::createFromArray($end1),
            excludeBoundaries: $excludeBoundaries1
        );
        $period2 = new Period(
            Date::createFromArray($start2),
            Date::createFromArray($end2),
            excludeBoundaries: $excludeBoundaries2
        );

        $this->assertSame($expected, $period1->contains($period2));
    }

    public function testContainsInvalidGranularity(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIs('Period granularity `day` must match other period granularity `hour`.');

        $period1 = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 10])
        );
        $period2 = new Period(
            DateTime::createFromArray([2022, 1, 15]),
            DateTime::createFromArray([2022, 1, 20]),
            'hour'
        );

        $period1->contains($period2);
    }
}
