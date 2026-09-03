<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\PeriodCollection;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use Fyre\Utility\DateTime\PeriodCollection;
use PHPUnit\Framework\Attributes\DataProvider;

use function count;

trait GapsTestTrait
{
    /**
     * @return array<string, array{array<int, array{int[], int[], 'end'|'none'|'start'}>, array<int, array{int[], int[], 'both'|'none'}>}>
     */
    public static function gapsProvider(): array
    {
        return [
            'gap' => [
                [
                    [[2022, 1, 1], [2022, 1, 5], 'none'],
                    [[2022, 1, 10], [2022, 1, 15], 'none'],
                ],
                [
                    [[2022, 1, 5], [2022, 1, 10], 'both'],
                ],
            ],
            'excluded boundaries' => [
                [
                    [[2022, 1, 1], [2022, 1, 5], 'end'],
                    [[2022, 1, 10], [2022, 1, 15], 'start'],
                ],
                [
                    [[2022, 1, 5], [2022, 1, 10], 'none'],
                ],
            ],
            'multiple gaps' => [
                [
                    [[2022, 1, 1], [2022, 1, 5], 'none'],
                    [[2022, 1, 10], [2022, 1, 15], 'none'],
                    [[2022, 1, 20], [2022, 1, 25], 'none'],
                ],
                [
                    [[2022, 1, 5], [2022, 1, 10], 'both'],
                    [[2022, 1, 15], [2022, 1, 20], 'both'],
                ],
            ],
            'empty' => [[], []],
        ];
    }

    /**
     * @param array<int, array{int[], int[], 'end'|'none'|'start'}> $periodData
     * @param array<int, array{int[], int[], 'both'|'none'}> $expected
     */
    #[DataProvider('gapsProvider')]
    public function testGaps(array $periodData, array $expected): void
    {
        $periods = [];

        foreach ($periodData as [$start, $end, $boundaries]) {
            $periods[] = new Period(
                DateTime::createFromArray($start),
                DateTime::createFromArray($end),
                excludeBoundaries: $boundaries
            );
        }

        $gaps = new PeriodCollection(...$periods)->gaps();

        $this->assertCount(count($expected), $gaps);

        foreach ($expected as $index => [$start, $end, $boundaries]) {
            $gap = $gaps->get($index);

            $this->assertInstanceOf(DateTime::class, $gap->start());
            $this->assertInstanceOf(DateTime::class, $gap->end());
            $this->assertSame(DateTime::createFromArray($start)->toIsoString(), $gap->start()->toIsoString());
            $this->assertSame(DateTime::createFromArray($end)->toIsoString(), $gap->end()->toIsoString());
            $this->assertSame($boundaries, Period::getBoundaries($gap->includesStart(), $gap->includesEnd()));
        }
    }

    /**
     * @param array<int, array{int[], int[], 'end'|'none'|'start'}> $periodData
     * @param array<int, array{int[], int[], 'both'|'none'}> $expected
     */
    #[DataProvider('gapsProvider')]
    public function testGapsDate(array $periodData, array $expected): void
    {
        $periods = [];

        foreach ($periodData as [$start, $end, $boundaries]) {
            $periods[] = new Period(
                Date::createFromArray($start),
                Date::createFromArray($end),
                excludeBoundaries: $boundaries
            );
        }

        $gaps = new PeriodCollection(...$periods)->gaps();

        $this->assertCount(count($expected), $gaps);

        foreach ($expected as $index => [$start, $end, $boundaries]) {
            $gap = $gaps->get($index);

            $this->assertInstanceOf(Date::class, $gap->start());
            $this->assertInstanceOf(Date::class, $gap->end());
            $this->assertSame(Date::createFromArray($start)->toIsoString(), $gap->start()->toIsoString());
            $this->assertSame(Date::createFromArray($end)->toIsoString(), $gap->end()->toIsoString());
            $this->assertSame($boundaries, Period::getBoundaries($gap->includesStart(), $gap->includesEnd()));
        }
    }
}
