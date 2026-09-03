<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;

use function count;

trait OverlapAnyTestTrait
{
    /**
     * @return array<string, array{int[], int[], array<int, array{int[], int[]}>, array<int, array{int[], int[]}>}>
     */
    public static function overlapAnyProvider(): array
    {
        return [
            'overlaps' => [
                [2022, 1, 1],
                [2022, 1, 20],
                [
                    [[2022, 1, 10], [2022, 1, 20]],
                    [[2022, 1, 5], [2022, 1, 15]],
                ],
                [
                    [[2022, 1, 10], [2022, 1, 20]],
                    [[2022, 1, 5], [2022, 1, 15]],
                ],
            ],
            'no overlaps' => [
                [2022, 1, 1],
                [2022, 1, 5],
                [
                    [[2022, 1, 10], [2022, 1, 15]],
                    [[2022, 1, 15], [2022, 1, 20]],
                ],
                [],
            ],
        ];
    }

    /**
     * @param int[] $start
     * @param int[] $end
     * @param array<int, array{int[], int[]}> $otherData
     * @param array<int, array{int[], int[]}> $expected
     */
    #[DataProvider('overlapAnyProvider')]
    public function testOverlapAny(array $start, array $end, array $otherData, array $expected): void
    {
        $period = new Period(
            DateTime::createFromArray($start),
            DateTime::createFromArray($end)
        );
        $others = [];

        foreach ($otherData as [$otherStart, $otherEnd]) {
            $others[] = new Period(
                DateTime::createFromArray($otherStart),
                DateTime::createFromArray($otherEnd)
            );
        }

        $overlaps = $period->overlapAny(...$others);

        $this->assertCount(count($expected), $overlaps);

        foreach ($expected as $index => [$expectedStart, $expectedEnd]) {
            $overlap = $overlaps->get($index);

            $this->assertInstanceOf(DateTime::class, $overlap->start());
            $this->assertInstanceOf(DateTime::class, $overlap->end());
            $this->assertSame(DateTime::createFromArray($expectedStart)->toIsoString(), $overlap->start()->toIsoString());
            $this->assertSame(DateTime::createFromArray($expectedEnd)->toIsoString(), $overlap->end()->toIsoString());
        }
    }

    /**
     * @param int[] $start
     * @param int[] $end
     * @param array<int, array{int[], int[]}> $otherData
     * @param array<int, array{int[], int[]}> $expected
     */
    #[DataProvider('overlapAnyProvider')]
    public function testOverlapAnyDate(array $start, array $end, array $otherData, array $expected): void
    {
        $period = new Period(
            Date::createFromArray($start),
            Date::createFromArray($end)
        );
        $others = [];

        foreach ($otherData as [$otherStart, $otherEnd]) {
            $others[] = new Period(
                Date::createFromArray($otherStart),
                Date::createFromArray($otherEnd)
            );
        }

        $overlaps = $period->overlapAny(...$others);

        $this->assertCount(count($expected), $overlaps);

        foreach ($expected as $index => [$expectedStart, $expectedEnd]) {
            $overlap = $overlaps->get($index);

            $this->assertInstanceOf(Date::class, $overlap->start());
            $this->assertInstanceOf(Date::class, $overlap->end());
            $this->assertSame(Date::createFromArray($expectedStart)->toIsoString(), $overlap->start()->toIsoString());
            $this->assertSame(Date::createFromArray($expectedEnd)->toIsoString(), $overlap->end()->toIsoString());
        }
    }

    public function testOverlapAnyInvalidGranularity(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIs('Period granularity `day` must match other period granularity `hour`.');

        $period1 = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 30])
        );
        $period2 = new Period(
            DateTime::createFromArray([2022, 1, 5]),
            DateTime::createFromArray([2022, 1, 10])
        );
        $period3 = new Period(
            DateTime::createFromArray([2022, 1, 15]),
            DateTime::createFromArray([2022, 1, 20]),
            'hour'
        );

        $period1->overlapAny($period2, $period3);
    }
}
