<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;

use function count;

trait DiffSymmetricTestTrait
{
    /**
     * @return array<string, array{int[], int[], 'end'|'none', int[], int[], 'end'|'none'|'start', array<int, array{int[], int[], 'end'|'none'|'start'}>}>
     */
    public static function diffSymmetricProvider(): array
    {
        return [
            'overlap' => [
                [2022, 1, 1],
                [2022, 1, 15],
                'none',
                [2022, 1, 10],
                [2022, 1, 20],
                'none',
                [
                    [[2022, 1, 1], [2022, 1, 10], 'end'],
                    [[2022, 1, 15], [2022, 1, 20], 'start'],
                ],
            ],
            'first excludes end' => [
                [2022, 1, 1],
                [2022, 1, 15],
                'end',
                [2022, 1, 10],
                [2022, 1, 20],
                'none',
                [
                    [[2022, 1, 1], [2022, 1, 10], 'end'],
                    [[2022, 1, 15], [2022, 1, 20], 'none'],
                ],
            ],
            'second excludes end' => [
                [2022, 1, 1],
                [2022, 1, 20],
                'none',
                [2022, 1, 10],
                [2022, 1, 15],
                'end',
                [
                    [[2022, 1, 1], [2022, 1, 10], 'end'],
                    [[2022, 1, 15], [2022, 1, 20], 'none'],
                ],
            ],
            'second excludes start' => [
                [2022, 1, 1],
                [2022, 1, 15],
                'none',
                [2022, 1, 10],
                [2022, 1, 20],
                'start',
                [
                    [[2022, 1, 1], [2022, 1, 10], 'none'],
                    [[2022, 1, 15], [2022, 1, 20], 'start'],
                ],
            ],
            'first inside second' => [
                [2022, 1, 10],
                [2022, 1, 15],
                'none',
                [2022, 1, 1],
                [2022, 1, 20],
                'none',
                [
                    [[2022, 1, 1], [2022, 1, 10], 'end'],
                    [[2022, 1, 15], [2022, 1, 20], 'start'],
                ],
            ],
            'no overlap' => [
                [2022, 1, 1],
                [2022, 1, 10],
                'none',
                [2022, 1, 15],
                [2022, 1, 20],
                'none',
                [
                    [[2022, 1, 1], [2022, 1, 10], 'none'],
                    [[2022, 1, 15], [2022, 1, 20], 'none'],
                ],
            ],
        ];
    }

    /**
     * @param int[] $start1
     * @param int[] $end1
     * @param 'end'|'none' $excludeBoundaries1
     * @param int[] $start2
     * @param int[] $end2
     * @param 'end'|'none'|'start' $excludeBoundaries2
     * @param array<int, array{int[], int[], 'end'|'none'|'start'}> $expected
     */
    #[DataProvider('diffSymmetricProvider')]
    public function testDiffSymmetric(array $start1, array $end1, string $excludeBoundaries1, array $start2, array $end2, string $excludeBoundaries2, array $expected): void
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

        $collection = $period1->diffSymmetric($period2);

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
     * @param 'end'|'none' $excludeBoundaries1
     * @param int[] $start2
     * @param int[] $end2
     * @param 'end'|'none'|'start' $excludeBoundaries2
     * @param array<int, array{int[], int[], 'end'|'none'|'start'}> $expected
     */
    #[DataProvider('diffSymmetricProvider')]
    public function testDiffSymmetricDate(array $start1, array $end1, string $excludeBoundaries1, array $start2, array $end2, string $excludeBoundaries2, array $expected): void
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

        $collection = $period1->diffSymmetric($period2);

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

    public function testDiffSymmetricInvalidGranularity(): void
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

        $period1->diffSymmetric($period2);
    }
}
