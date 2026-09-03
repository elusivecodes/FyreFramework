<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;

trait OverlapAllTestTrait
{
    /**
     * @return array<string, array{int[], int[], array<int, array{int[], int[]}>, array{int[], int[]}|null}>
     */
    public static function periodOverlapAllProvider(): array
    {
        return [
            'overlap' => [
                [2022, 1, 1],
                [2022, 1, 20],
                [
                    [[2022, 1, 10], [2022, 1, 20]],
                    [[2022, 1, 5], [2022, 1, 15]],
                ],
                [[2022, 1, 10], [2022, 1, 15]],
            ],
            'no overlap' => [
                [2022, 1, 1],
                [2022, 1, 10],
                [
                    [[2022, 1, 5], [2022, 1, 20]],
                    [[2022, 1, 25], [2022, 1, 30]],
                ],
                null,
            ],
            'no other periods' => [
                [2022, 1, 1],
                [2022, 1, 10],
                [],
                [[2022, 1, 1], [2022, 1, 10]],
            ],
        ];
    }

    /**
     * @param int[] $start
     * @param int[] $end
     * @param array<int, array{int[], int[]}> $otherData
     * @param array{int[], int[]}|null $expected
     */
    #[DataProvider('periodOverlapAllProvider')]
    public function testOverlapAll(array $start, array $end, array $otherData, array|null $expected): void
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

        $overlap = $period->overlapAll(...$others);

        if ($expected === null) {
            $this->assertNull($overlap);

            return;
        }

        $this->assertInstanceOf(Period::class, $overlap);
        $this->assertInstanceOf(DateTime::class, $overlap->start());
        $this->assertInstanceOf(DateTime::class, $overlap->end());
        $this->assertSame(DateTime::createFromArray($expected[0])->toIsoString(), $overlap->start()->toIsoString());
        $this->assertSame(DateTime::createFromArray($expected[1])->toIsoString(), $overlap->end()->toIsoString());
    }

    /**
     * @param int[] $start
     * @param int[] $end
     * @param array<int, array{int[], int[]}> $otherData
     * @param array{int[], int[]}|null $expected
     */
    #[DataProvider('periodOverlapAllProvider')]
    public function testOverlapAllDate(array $start, array $end, array $otherData, array|null $expected): void
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

        $overlap = $period->overlapAll(...$others);

        if ($expected === null) {
            $this->assertNull($overlap);

            return;
        }

        $this->assertInstanceOf(Period::class, $overlap);
        $this->assertInstanceOf(Date::class, $overlap->start());
        $this->assertInstanceOf(Date::class, $overlap->end());
        $this->assertSame(Date::createFromArray($expected[0])->toIsoString(), $overlap->start()->toIsoString());
        $this->assertSame(Date::createFromArray($expected[1])->toIsoString(), $overlap->end()->toIsoString());
    }

    public function testOverlapAllInvalidGranularity(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIs('Period granularity `day` must match other period granularity `hour`.');

        $period1 = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 10])
        );
        $period2 = new Period(
            DateTime::createFromArray([2022, 1, 5]),
            DateTime::createFromArray([2022, 1, 20])
        );
        $period3 = new Period(
            DateTime::createFromArray([2022, 1, 25]),
            DateTime::createFromArray([2022, 1, 30]),
            'hour'
        );

        $period1->overlapAll($period2, $period3);
    }
}
