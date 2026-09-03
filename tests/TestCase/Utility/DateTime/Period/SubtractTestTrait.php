<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;

use function count;

trait SubtractTestTrait
{
    /**
     * @return array<string, array{int[], int[], int[], int[], 'both'|'none'|'start', array<int, array{int[], int[], 'end'|'none'|'start'}>}>
     */
    public static function subtractProvider(): array
    {
        return [
            'end after' => [
                [2022, 1, 1],
                [2022, 1, 15],
                [2022, 1, 10],
                [2022, 1, 20],
                'none',
                [
                    [[2022, 1, 1], [2022, 1, 10], 'end'],
                ],
            ],
            'end after excluded start' => [
                [2022, 1, 1],
                [2022, 1, 15],
                [2022, 1, 10],
                [2022, 1, 20],
                'start',
                [
                    [[2022, 1, 1], [2022, 1, 10], 'none'],
                ],
            ],
            'contained' => [
                [2022, 1, 1],
                [2022, 1, 20],
                [2022, 1, 10],
                [2022, 1, 15],
                'none',
                [
                    [[2022, 1, 1], [2022, 1, 10], 'end'],
                    [[2022, 1, 15], [2022, 1, 20], 'start'],
                ],
            ],
            'contained excluded both' => [
                [2022, 1, 1],
                [2022, 1, 20],
                [2022, 1, 10],
                [2022, 1, 15],
                'both',
                [
                    [[2022, 1, 1], [2022, 1, 10], 'none'],
                    [[2022, 1, 15], [2022, 1, 20], 'none'],
                ],
            ],
            'no overlap' => [
                [2022, 1, 1],
                [2022, 1, 10],
                [2022, 1, 15],
                [2022, 1, 20],
                'none',
                [
                    [[2022, 1, 1], [2022, 1, 10], 'none'],
                ],
            ],
            'fully subtracted' => [
                [2022, 1, 10],
                [2022, 1, 15],
                [2022, 1, 1],
                [2022, 1, 20],
                'none',
                [],
            ],
        ];
    }

    /**
     * @param int[] $start1
     * @param int[] $end1
     * @param int[] $start2
     * @param int[] $end2
     * @param 'both'|'none'|'start' $excludeBoundaries
     * @param array<int, array{int[], int[], 'end'|'none'|'start'}> $expected
     */
    #[DataProvider('subtractProvider')]
    public function testSubtract(array $start1, array $end1, array $start2, array $end2, string $excludeBoundaries, array $expected): void
    {
        $period1 = new Period(
            DateTime::createFromArray($start1),
            DateTime::createFromArray($end1)
        );
        $period2 = new Period(
            DateTime::createFromArray($start2),
            DateTime::createFromArray($end2),
            excludeBoundaries: $excludeBoundaries
        );

        $collection = $period1->subtract($period2);

        $this->assertCount(count($expected), $collection);

        foreach ($expected as $index => [$start, $end, $boundaries]) {
            $period = $collection->get($index);

            $this->assertInstanceOf(DateTime::class, $period->start());
            $this->assertInstanceOf(DateTime::class, $period->end());
            $this->assertSame(DateTime::createFromArray($start)->toIsoString(), $period->start()->toIsoString());
            $this->assertSame(DateTime::createFromArray($end)->toIsoString(), $period->end()->toIsoString());
            $this->assertSame($boundaries, Period::getBoundaries($period->includesStart(), $period->includesEnd()));
        }
    }

    /**
     * @param int[] $start1
     * @param int[] $end1
     * @param int[] $start2
     * @param int[] $end2
     * @param 'both'|'none'|'start' $excludeBoundaries
     * @param array<int, array{int[], int[], 'end'|'none'|'start'}> $expected
     */
    #[DataProvider('subtractProvider')]
    public function testSubtractDate(array $start1, array $end1, array $start2, array $end2, string $excludeBoundaries, array $expected): void
    {
        $period1 = new Period(
            Date::createFromArray($start1),
            Date::createFromArray($end1)
        );
        $period2 = new Period(
            Date::createFromArray($start2),
            Date::createFromArray($end2),
            excludeBoundaries: $excludeBoundaries
        );

        $collection = $period1->subtract($period2);

        $this->assertCount(count($expected), $collection);

        foreach ($expected as $index => [$start, $end, $boundaries]) {
            $period = $collection->get($index);

            $this->assertInstanceOf(Date::class, $period->start());
            $this->assertInstanceOf(Date::class, $period->end());
            $this->assertSame(Date::createFromArray($start)->toIsoString(), $period->start()->toIsoString());
            $this->assertSame(Date::createFromArray($end)->toIsoString(), $period->end()->toIsoString());
            $this->assertSame($boundaries, Period::getBoundaries($period->includesStart(), $period->includesEnd()));
        }
    }

    public function testSubtractInvalidGranularity(): void
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

        $period1->subtract($period2);
    }
}
