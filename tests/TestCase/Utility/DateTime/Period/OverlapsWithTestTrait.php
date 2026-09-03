<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;

trait OverlapsWithTestTrait
{
    /**
     * @return array<string, array{int[], int[], 'end'|'none'|'start', int[], int[], 'end'|'none'|'start', bool}>
     */
    public static function overlapsWithProvider(): array
    {
        return [
            'touching end' => [
                [2022, 1, 1],
                [2022, 1, 15],
                'none',
                [2022, 1, 15],
                [2022, 1, 30],
                'none',
                true,
            ],
            'after end' => [
                [2022, 1, 1],
                [2022, 1, 15],
                'none',
                [2022, 1, 16],
                [2022, 1, 30],
                'none',
                false,
            ],
            'excluded end' => [
                [2022, 1, 1],
                [2022, 1, 15],
                'end',
                [2022, 1, 15],
                [2022, 1, 30],
                'none',
                false,
            ],
            'excluded other start' => [
                [2022, 1, 1],
                [2022, 1, 15],
                'none',
                [2022, 1, 15],
                [2022, 1, 30],
                'start',
                false,
            ],
            'touching start' => [
                [2022, 1, 1],
                [2022, 1, 15],
                'none',
                [2021, 12, 15],
                [2022, 1, 1],
                'none',
                true,
            ],
            'before start' => [
                [2022, 1, 1],
                [2022, 1, 15],
                'none',
                [2021, 12, 15],
                [2021, 12, 31],
                'none',
                false,
            ],
            'excluded other end' => [
                [2022, 1, 1],
                [2022, 1, 15],
                'none',
                [2021, 12, 15],
                [2022, 1, 1],
                'end',
                false,
            ],
            'excluded start' => [
                [2022, 1, 1],
                [2022, 1, 15],
                'start',
                [2021, 12, 15],
                [2022, 1, 1],
                'none',
                false,
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
    #[DataProvider('overlapsWithProvider')]
    public function testOverlapsWith(array $start1, array $end1, string $excludeBoundaries1, array $start2, array $end2, string $excludeBoundaries2, bool $expected): void
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

        $this->assertSame($expected, $period1->overlapsWith($period2));
    }

    /**
     * @param int[] $start1
     * @param int[] $end1
     * @param 'end'|'none'|'start' $excludeBoundaries1
     * @param int[] $start2
     * @param int[] $end2
     * @param 'end'|'none'|'start' $excludeBoundaries2
     */
    #[DataProvider('overlapsWithProvider')]
    public function testOverlapsWithDate(array $start1, array $end1, string $excludeBoundaries1, array $start2, array $end2, string $excludeBoundaries2, bool $expected): void
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

        $this->assertSame($expected, $period1->overlapsWith($period2));
    }

    public function testOverlapsWithInvalidGranularity(): void
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

        $period1->overlapsWith($period2);
    }
}
