<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;

trait TouchesTestTrait
{
    /**
     * @return array<string, array{int[], int[], 'none'|'start', int[], int[], 'end'|'none', bool}>
     */
    public static function touchesProvider(): array
    {
        return [
            'touching end' => [
                [2022, 1, 10],
                [2022, 1, 20],
                'none',
                [2022, 1, 1],
                [2022, 1, 10],
                'none',
                true,
            ],
            'after end' => [
                [2022, 1, 11],
                [2022, 1, 20],
                'none',
                [2022, 1, 1],
                [2022, 1, 10],
                'none',
                false,
            ],
            'overlapping end' => [
                [2022, 1, 9],
                [2022, 1, 20],
                'none',
                [2022, 1, 1],
                [2022, 1, 10],
                'none',
                false,
            ],
            'excluded other end' => [
                [2022, 1, 9],
                [2022, 1, 20],
                'none',
                [2022, 1, 1],
                [2022, 1, 10],
                'end',
                true,
            ],
            'excluded start' => [
                [2022, 1, 9],
                [2022, 1, 20],
                'start',
                [2022, 1, 1],
                [2022, 1, 10],
                'none',
                true,
            ],
            'touching start' => [
                [2022, 1, 1],
                [2022, 1, 10],
                'none',
                [2022, 1, 10],
                [2022, 1, 20],
                'none',
                true,
            ],
        ];
    }

    /**
     * @param int[] $start1
     * @param int[] $end1
     * @param 'none'|'start' $excludeBoundaries1
     * @param int[] $start2
     * @param int[] $end2
     * @param 'end'|'none' $excludeBoundaries2
     */
    #[DataProvider('touchesProvider')]
    public function testTouches(array $start1, array $end1, string $excludeBoundaries1, array $start2, array $end2, string $excludeBoundaries2, bool $expected): void
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

        $this->assertSame($expected, $period1->touches($period2));
    }

    /**
     * @param int[] $start1
     * @param int[] $end1
     * @param 'none'|'start' $excludeBoundaries1
     * @param int[] $start2
     * @param int[] $end2
     * @param 'end'|'none' $excludeBoundaries2
     */
    #[DataProvider('touchesProvider')]
    public function testTouchesDate(array $start1, array $end1, string $excludeBoundaries1, array $start2, array $end2, string $excludeBoundaries2, bool $expected): void
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

        $this->assertSame($expected, $period1->touches($period2));
    }

    public function testTouchesInvalidGranularity(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessageIs('Period granularity `day` must match other period granularity `hour`.');

        $period1 = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 10])
        );
        $period2 = new Period(
            DateTime::createFromArray([2022, 1, 10]),
            DateTime::createFromArray([2022, 1, 20]),
            'hour'
        );

        $period1->touches($period2);
    }
}
