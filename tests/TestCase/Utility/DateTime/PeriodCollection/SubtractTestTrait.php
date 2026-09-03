<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\PeriodCollection;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use Fyre\Utility\DateTime\PeriodCollection;
use PHPUnit\Framework\Attributes\DataProvider;

use function count;

trait SubtractTestTrait
{
    /**
     * @return array<string, array{array<int, array{int[], int[]}>, array<int, array{int[], int[]}>, array<int, array{int[], int[], 'end'|'none'|'start'}>}>
     */
    public static function collectionSubtractProvider(): array
    {
        return [
            'subtract' => [
                [
                    [[2022, 1, 1], [2022, 1, 5]],
                    [[2022, 1, 10], [2022, 1, 15]],
                ],
                [
                    [[2022, 1, 2], [2022, 1, 3]],
                    [[2022, 1, 12], [2022, 1, 13]],
                ],
                [
                    [[2022, 1, 1], [2022, 1, 2], 'end'],
                    [[2022, 1, 3], [2022, 1, 5], 'start'],
                    [[2022, 1, 10], [2022, 1, 12], 'end'],
                    [[2022, 1, 13], [2022, 1, 15], 'start'],
                ],
            ],
            'empty subtraction' => [
                [
                    [[2022, 1, 1], [2022, 1, 5]],
                    [[2022, 1, 10], [2022, 1, 15]],
                ],
                [],
                [
                    [[2022, 1, 1], [2022, 1, 5], 'none'],
                    [[2022, 1, 10], [2022, 1, 15], 'none'],
                ],
            ],
        ];
    }

    /**
     * @param array<int, array{int[], int[]}> $periodData
     * @param array<int, array{int[], int[]}> $otherData
     * @param array<int, array{int[], int[], 'end'|'none'|'start'}> $expected
     */
    #[DataProvider('collectionSubtractProvider')]
    public function testSubtract(array $periodData, array $otherData, array $expected): void
    {
        $periods = [];
        foreach ($periodData as [$start, $end]) {
            $periods[] = new Period(
                DateTime::createFromArray($start),
                DateTime::createFromArray($end)
            );
        }

        $others = [];
        foreach ($otherData as [$start, $end]) {
            $others[] = new Period(
                DateTime::createFromArray($start),
                DateTime::createFromArray($end)
            );
        }

        $subtractions = new PeriodCollection(...$periods)->subtract(new PeriodCollection(...$others));

        $this->assertCount(count($expected), $subtractions);

        foreach ($expected as $index => [$start, $end, $boundaries]) {
            $subtraction = $subtractions->get($index);

            $this->assertInstanceOf(DateTime::class, $subtraction->start());
            $this->assertInstanceOf(DateTime::class, $subtraction->end());
            $this->assertSame(DateTime::createFromArray($start)->toIsoString(), $subtraction->start()->toIsoString());
            $this->assertSame(DateTime::createFromArray($end)->toIsoString(), $subtraction->end()->toIsoString());
            $this->assertSame($boundaries, Period::getBoundaries($subtraction->includesStart(), $subtraction->includesEnd()));
        }
    }

    /**
     * @param array<int, array{int[], int[]}> $periodData
     * @param array<int, array{int[], int[]}> $otherData
     * @param array<int, array{int[], int[], 'end'|'none'|'start'}> $expected
     */
    #[DataProvider('collectionSubtractProvider')]
    public function testSubtractDate(array $periodData, array $otherData, array $expected): void
    {
        $periods = [];
        foreach ($periodData as [$start, $end]) {
            $periods[] = new Period(
                Date::createFromArray($start),
                Date::createFromArray($end)
            );
        }

        $others = [];
        foreach ($otherData as [$start, $end]) {
            $others[] = new Period(
                Date::createFromArray($start),
                Date::createFromArray($end)
            );
        }

        $subtractions = new PeriodCollection(...$periods)->subtract(new PeriodCollection(...$others));

        $this->assertCount(count($expected), $subtractions);

        foreach ($expected as $index => [$start, $end, $boundaries]) {
            $subtraction = $subtractions->get($index);

            $this->assertInstanceOf(Date::class, $subtraction->start());
            $this->assertInstanceOf(Date::class, $subtraction->end());
            $this->assertSame(Date::createFromArray($start)->toIsoString(), $subtraction->start()->toIsoString());
            $this->assertSame(Date::createFromArray($end)->toIsoString(), $subtraction->end()->toIsoString());
            $this->assertSame($boundaries, Period::getBoundaries($subtraction->includesStart(), $subtraction->includesEnd()));
        }
    }
}
