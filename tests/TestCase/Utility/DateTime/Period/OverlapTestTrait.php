<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;

trait OverlapTestTrait
{
    /**
     * @return array<string, array{int[], int[], 'end'|'none'|'start', int[], int[], 'end'|'none'|'start', array{int[], int[], 'end'|'none'|'start'}|null}>
     */
    public static function overlapProvider(): array
    {
        return [
            'overlap' => [
                [2022, 1, 1],
                [2022, 1, 15],
                'none',
                [2022, 1, 10],
                [2022, 1, 20],
                'none',
                [[2022, 1, 10], [2022, 1, 15], 'none'],
            ],
            'excluded end' => [
                [2022, 1, 1],
                [2022, 1, 15],
                'end',
                [2022, 1, 10],
                [2022, 1, 20],
                'none',
                [[2022, 1, 10], [2022, 1, 15], 'end'],
            ],
            'contained' => [
                [2022, 1, 1],
                [2022, 1, 20],
                'none',
                [2022, 1, 10],
                [2022, 1, 15],
                'none',
                [[2022, 1, 10], [2022, 1, 15], 'none'],
            ],
            'contained excluded end' => [
                [2022, 1, 1],
                [2022, 1, 20],
                'none',
                [2022, 1, 10],
                [2022, 1, 15],
                'end',
                [[2022, 1, 10], [2022, 1, 15], 'end'],
            ],
            'excluded other start' => [
                [2022, 1, 1],
                [2022, 1, 15],
                'none',
                [2022, 1, 10],
                [2022, 1, 20],
                'start',
                [[2022, 1, 10], [2022, 1, 15], 'start'],
            ],
            'inside other' => [
                [2022, 1, 10],
                [2022, 1, 15],
                'none',
                [2022, 1, 1],
                [2022, 1, 20],
                'none',
                [[2022, 1, 10], [2022, 1, 15], 'none'],
            ],
            'inside other excluded start' => [
                [2022, 1, 10],
                [2022, 1, 15],
                'start',
                [2022, 1, 1],
                [2022, 1, 20],
                'none',
                [[2022, 1, 10], [2022, 1, 15], 'start'],
            ],
            'no overlap' => [
                [2022, 1, 1],
                [2022, 1, 10],
                'none',
                [2022, 1, 15],
                [2022, 1, 20],
                'none',
                null,
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
     * @param array{int[], int[], 'end'|'none'|'start'}|null $expected
     */
    #[DataProvider('overlapProvider')]
    public function testOverlap(array $start1, array $end1, string $excludeBoundaries1, array $start2, array $end2, string $excludeBoundaries2, array|null $expected): void
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

        $overlap = $period1->overlap($period2);

        if ($expected === null) {
            $this->assertNull($overlap);

            return;
        }

        $this->assertInstanceOf(Period::class, $overlap);
        $this->assertInstanceOf(DateTime::class, $overlap->start());
        $this->assertInstanceOf(DateTime::class, $overlap->end());
        $this->assertSame(DateTime::createFromArray($expected[0])->toIsoString(), $overlap->start()->toIsoString());
        $this->assertSame(DateTime::createFromArray($expected[1])->toIsoString(), $overlap->end()->toIsoString());
        $this->assertSame($expected[2], Period::getBoundaries($overlap->includesStart(), $overlap->includesEnd()));
    }

    /**
     * @param int[] $start1
     * @param int[] $end1
     * @param 'end'|'none'|'start' $excludeBoundaries1
     * @param int[] $start2
     * @param int[] $end2
     * @param 'end'|'none'|'start' $excludeBoundaries2
     * @param array{int[], int[], 'end'|'none'|'start'}|null $expected
     */
    #[DataProvider('overlapProvider')]
    public function testOverlapDate(array $start1, array $end1, string $excludeBoundaries1, array $start2, array $end2, string $excludeBoundaries2, array|null $expected): void
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

        $overlap = $period1->overlap($period2);

        if ($expected === null) {
            $this->assertNull($overlap);

            return;
        }

        $this->assertInstanceOf(Period::class, $overlap);
        $this->assertInstanceOf(Date::class, $overlap->start());
        $this->assertInstanceOf(Date::class, $overlap->end());
        $this->assertSame(Date::createFromArray($expected[0])->toIsoString(), $overlap->start()->toIsoString());
        $this->assertSame(Date::createFromArray($expected[1])->toIsoString(), $overlap->end()->toIsoString());
        $this->assertSame($expected[2], Period::getBoundaries($overlap->includesStart(), $overlap->includesEnd()));
    }

    public function testOverlapInvalidGranularity(): void
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

        $period1->overlap($period2);
    }
}
