<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\PeriodCollection;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use Fyre\Utility\DateTime\PeriodCollection;
use PHPUnit\Framework\Attributes\DataProvider;

use function count;

trait IntersectTestTrait
{
    /**
     * @return array<string, array{array<int, array{int[], int[], 'both'|'none'}>, array{int[], int[], 'both'|'none'}, array<int, array{int[], int[], 'both'|'none'}>}>
     */
    public static function intersectProvider(): array
    {
        return [
            'intersections' => [
                [
                    [[2022, 1, 1], [2022, 1, 5], 'none'],
                    [[2022, 1, 10], [2022, 1, 15], 'none'],
                ],
                [[2022, 1, 3], [2022, 1, 13], 'none'],
                [
                    [[2022, 1, 3], [2022, 1, 5], 'none'],
                    [[2022, 1, 10], [2022, 1, 13], 'none'],
                ],
            ],
            'excluded boundaries' => [
                [
                    [[2022, 1, 1], [2022, 1, 5], 'both'],
                    [[2022, 1, 10], [2022, 1, 15], 'both'],
                ],
                [[2022, 1, 3], [2022, 1, 13], 'both'],
                [
                    [[2022, 1, 3], [2022, 1, 5], 'both'],
                    [[2022, 1, 10], [2022, 1, 13], 'both'],
                ],
            ],
            'no intersection' => [
                [
                    [[2022, 1, 1], [2022, 1, 5], 'none'],
                ],
                [[2022, 1, 10], [2022, 1, 15], 'none'],
                [],
            ],
            'empty' => [[], [[2022, 1, 3], [2022, 1, 13], 'none'], []],
        ];
    }

    /**
     * @param array<int, array{int[], int[], 'both'|'none'}> $periodData
     * @param array{int[], int[], 'both'|'none'} $otherData
     * @param array<int, array{int[], int[], 'both'|'none'}> $expected
     */
    #[DataProvider('intersectProvider')]
    public function testIntersect(array $periodData, array $otherData, array $expected): void
    {
        $periods = [];

        foreach ($periodData as [$start, $end, $boundaries]) {
            $periods[] = new Period(
                DateTime::createFromArray($start),
                DateTime::createFromArray($end),
                excludeBoundaries: $boundaries
            );
        }

        [$otherStart, $otherEnd, $otherBoundaries] = $otherData;
        $other = new Period(
            DateTime::createFromArray($otherStart),
            DateTime::createFromArray($otherEnd),
            excludeBoundaries: $otherBoundaries
        );

        $intersections = new PeriodCollection(...$periods)->intersect($other);

        $this->assertCount(count($expected), $intersections);

        foreach ($expected as $index => [$start, $end, $boundaries]) {
            $intersection = $intersections->get($index);

            $this->assertInstanceOf(DateTime::class, $intersection->start());
            $this->assertInstanceOf(DateTime::class, $intersection->end());
            $this->assertSame(DateTime::createFromArray($start)->toIsoString(), $intersection->start()->toIsoString());
            $this->assertSame(DateTime::createFromArray($end)->toIsoString(), $intersection->end()->toIsoString());
            $this->assertSame($boundaries, Period::getBoundaries($intersection->includesStart(), $intersection->includesEnd()));
        }
    }

    /**
     * @param array<int, array{int[], int[], 'both'|'none'}> $periodData
     * @param array{int[], int[], 'both'|'none'} $otherData
     * @param array<int, array{int[], int[], 'both'|'none'}> $expected
     */
    #[DataProvider('intersectProvider')]
    public function testIntersectDate(array $periodData, array $otherData, array $expected): void
    {
        $periods = [];

        foreach ($periodData as [$start, $end, $boundaries]) {
            $periods[] = new Period(
                Date::createFromArray($start),
                Date::createFromArray($end),
                excludeBoundaries: $boundaries
            );
        }

        [$otherStart, $otherEnd, $otherBoundaries] = $otherData;
        $other = new Period(
            Date::createFromArray($otherStart),
            Date::createFromArray($otherEnd),
            excludeBoundaries: $otherBoundaries
        );

        $intersections = new PeriodCollection(...$periods)->intersect($other);

        $this->assertCount(count($expected), $intersections);

        foreach ($expected as $index => [$start, $end, $boundaries]) {
            $intersection = $intersections->get($index);

            $this->assertInstanceOf(Date::class, $intersection->start());
            $this->assertInstanceOf(Date::class, $intersection->end());
            $this->assertSame(Date::createFromArray($start)->toIsoString(), $intersection->start()->toIsoString());
            $this->assertSame(Date::createFromArray($end)->toIsoString(), $intersection->end()->toIsoString());
            $this->assertSame($boundaries, Period::getBoundaries($intersection->includesStart(), $intersection->includesEnd()));
        }
    }
}
