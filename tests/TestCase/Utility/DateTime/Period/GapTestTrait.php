<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;

trait GapTestTrait
{
    /**
     * @return array<string, array{int[], int[], 'end'|'none', int[], int[], 'none'|'start', array{int[], int[]}|null}>
     */
    public static function gapProvider(): array
    {
        return [
            'gap' => [
                [2022, 1, 1],
                [2022, 1, 10],
                'none',
                [2022, 1, 15],
                [2022, 1, 20],
                'none',
                [[2022, 1, 11], [2022, 1, 14]],
            ],
            'excluded first end' => [
                [2022, 1, 1],
                [2022, 1, 10],
                'end',
                [2022, 1, 15],
                [2022, 1, 20],
                'none',
                [[2022, 1, 10], [2022, 1, 14]],
            ],
            'excluded second start' => [
                [2022, 1, 1],
                [2022, 1, 10],
                'none',
                [2022, 1, 15],
                [2022, 1, 20],
                'start',
                [[2022, 1, 11], [2022, 1, 15]],
            ],
            'other starts first' => [
                [2022, 1, 15],
                [2022, 1, 20],
                'none',
                [2022, 1, 1],
                [2022, 1, 10],
                'none',
                [[2022, 1, 11], [2022, 1, 14]],
            ],
            'overlap' => [
                [2022, 1, 1],
                [2022, 1, 15],
                'none',
                [2022, 1, 10],
                [2022, 1, 20],
                'none',
                null,
            ],
            'touching' => [
                [2022, 1, 1],
                [2022, 1, 10],
                'none',
                [2022, 1, 10],
                [2022, 1, 20],
                'none',
                null,
            ],
            'no complete unit' => [
                [2022, 1, 1],
                [2022, 1, 10],
                'none',
                [2022, 1, 11],
                [2022, 1, 20],
                'none',
                null,
            ],
        ];
    }

    /**
     * @param int[] $start1
     * @param int[] $end1
     * @param 'end'|'none' $excludeBoundaries1
     * @param int[] $start2
     * @param int[] $end2
     * @param 'none'|'start' $excludeBoundaries2
     * @param array{int[], int[]}|null $expected
     */
    #[DataProvider('gapProvider')]
    public function testGap(array $start1, array $end1, string $excludeBoundaries1, array $start2, array $end2, string $excludeBoundaries2, array|null $expected): void
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

        $gap = $period1->gap($period2);

        if ($expected === null) {
            $this->assertNull($gap);

            return;
        }

        $this->assertInstanceOf(Period::class, $gap);
        $this->assertInstanceOf(DateTime::class, $gap->start());
        $this->assertInstanceOf(DateTime::class, $gap->end());
        $this->assertSame(DateTime::createFromArray($expected[0])->toIsoString(), $gap->start()->toIsoString());
        $this->assertSame(DateTime::createFromArray($expected[1])->toIsoString(), $gap->end()->toIsoString());
        $this->assertTrue($gap->includesStart());
        $this->assertTrue($gap->includesEnd());
    }

    /**
     * @param int[] $start1
     * @param int[] $end1
     * @param 'end'|'none' $excludeBoundaries1
     * @param int[] $start2
     * @param int[] $end2
     * @param 'none'|'start' $excludeBoundaries2
     * @param array{int[], int[]}|null $expected
     */
    #[DataProvider('gapProvider')]
    public function testGapDate(array $start1, array $end1, string $excludeBoundaries1, array $start2, array $end2, string $excludeBoundaries2, array|null $expected): void
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

        $gap = $period1->gap($period2);

        if ($expected === null) {
            $this->assertNull($gap);

            return;
        }

        $this->assertInstanceOf(Period::class, $gap);
        $this->assertInstanceOf(Date::class, $gap->start());
        $this->assertInstanceOf(Date::class, $gap->end());
        $this->assertSame(Date::createFromArray($expected[0])->toIsoString(), $gap->start()->toIsoString());
        $this->assertSame(Date::createFromArray($expected[1])->toIsoString(), $gap->end()->toIsoString());
        $this->assertTrue($gap->includesStart());
        $this->assertTrue($gap->includesEnd());
    }

    public function testGapInvalidGranularity(): void
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

        $period1->gap($period2);
    }
}
