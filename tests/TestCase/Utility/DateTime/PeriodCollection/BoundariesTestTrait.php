<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\PeriodCollection;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use Fyre\Utility\DateTime\PeriodCollection;
use PHPUnit\Framework\Attributes\DataProvider;

trait BoundariesTestTrait
{
    /**
     * @return array<string, array{int[], int[], 'end'|'none'|'start', int[], int[], 'end'|'none'|'start', 'both'|'none'}>
     */
    public static function boundariesProvider(): array
    {
        return [
            'ascending' => [
                [2022, 1, 1],
                [2022, 1, 10],
                'none',
                [2022, 1, 5],
                [2022, 1, 15],
                'none',
                'none',
            ],
            'descending' => [
                [2022, 1, 5],
                [2022, 1, 15],
                'none',
                [2022, 1, 1],
                [2022, 1, 10],
                'none',
                'none',
            ],
            'ascending excluded' => [
                [2022, 1, 1],
                [2022, 1, 10],
                'start',
                [2022, 1, 5],
                [2022, 1, 15],
                'end',
                'both',
            ],
            'descending excluded' => [
                [2022, 1, 5],
                [2022, 1, 15],
                'end',
                [2022, 1, 1],
                [2022, 1, 10],
                'start',
                'both',
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
     * @param 'both'|'none' $expectedBoundaries
     */
    #[DataProvider('boundariesProvider')]
    public function testBoundaries(array $start1, array $end1, string $excludeBoundaries1, array $start2, array $end2, string $excludeBoundaries2, string $expectedBoundaries): void
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

        $boundaries = new PeriodCollection($period1, $period2)->boundaries();

        $this->assertInstanceOf(Period::class, $boundaries);
        $this->assertInstanceOf(DateTime::class, $boundaries->start());
        $this->assertInstanceOf(DateTime::class, $boundaries->end());
        $this->assertSame('2022-01-01T00:00:00.000+00:00', $boundaries->start()->toIsoString());
        $this->assertSame('2022-01-15T00:00:00.000+00:00', $boundaries->end()->toIsoString());
        $this->assertSame($expectedBoundaries, Period::getBoundaries($boundaries->includesStart(), $boundaries->includesEnd()));
    }

    /**
     * @param int[] $start1
     * @param int[] $end1
     * @param 'end'|'none'|'start' $excludeBoundaries1
     * @param int[] $start2
     * @param int[] $end2
     * @param 'end'|'none'|'start' $excludeBoundaries2
     * @param 'both'|'none' $expectedBoundaries
     */
    #[DataProvider('boundariesProvider')]
    public function testBoundariesDate(array $start1, array $end1, string $excludeBoundaries1, array $start2, array $end2, string $excludeBoundaries2, string $expectedBoundaries): void
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

        $boundaries = new PeriodCollection($period1, $period2)->boundaries();

        $this->assertInstanceOf(Period::class, $boundaries);
        $this->assertInstanceOf(Date::class, $boundaries->start());
        $this->assertInstanceOf(Date::class, $boundaries->end());
        $this->assertSame('2022-01-01', $boundaries->start()->toIsoString());
        $this->assertSame('2022-01-15', $boundaries->end()->toIsoString());
        $this->assertSame($expectedBoundaries, Period::getBoundaries($boundaries->includesStart(), $boundaries->includesEnd()));
    }

    public function testBoundariesEmpty(): void
    {
        $this->assertNull(new PeriodCollection()->boundaries());
    }
}
