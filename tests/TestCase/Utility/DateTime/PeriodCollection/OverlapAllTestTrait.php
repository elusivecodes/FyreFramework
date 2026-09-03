<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\PeriodCollection;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use Fyre\Utility\DateTime\PeriodCollection;
use PHPUnit\Framework\Attributes\DataProvider;

use function array_slice;
use function count;

trait OverlapAllTestTrait
{
    /**
     * @return array<string, array{array<int, array<int, array{int[], int[], 'both'|'none'}>>, array<int, array{int[], int[], 'both'|'none'}>}>
     */
    public static function collectionOverlapAllProvider(): array
    {
        return [
            'overlap' => [
                [
                    [
                        [[2022, 1, 1], [2022, 1, 5], 'none'],
                        [[2022, 1, 10], [2022, 1, 15], 'none'],
                    ],
                    [
                        [[2022, 1, 3], [2022, 1, 8], 'none'],
                        [[2022, 1, 8], [2022, 1, 13], 'none'],
                    ],
                ],
                [
                    [[2022, 1, 3], [2022, 1, 5], 'none'],
                    [[2022, 1, 10], [2022, 1, 13], 'none'],
                ],
            ],
            'excluded boundaries' => [
                [
                    [
                        [[2022, 1, 1], [2022, 1, 5], 'both'],
                        [[2022, 1, 10], [2022, 1, 15], 'both'],
                    ],
                    [
                        [[2022, 1, 3], [2022, 1, 8], 'both'],
                        [[2022, 1, 8], [2022, 1, 13], 'both'],
                    ],
                ],
                [
                    [[2022, 1, 3], [2022, 1, 5], 'both'],
                    [[2022, 1, 10], [2022, 1, 13], 'both'],
                ],
            ],
            'multiple collections' => [
                [
                    [
                        [[2022, 1, 1], [2022, 1, 5], 'none'],
                        [[2022, 1, 10], [2022, 1, 15], 'none'],
                    ],
                    [
                        [[2022, 1, 3], [2022, 1, 8], 'none'],
                        [[2022, 1, 8], [2022, 1, 13], 'none'],
                    ],
                    [
                        [[2022, 1, 4], [2022, 1, 20], 'none'],
                    ],
                ],
                [
                    [[2022, 1, 4], [2022, 1, 5], 'none'],
                    [[2022, 1, 10], [2022, 1, 13], 'none'],
                ],
            ],
            'empty collection' => [
                [
                    [
                        [[2022, 1, 1], [2022, 1, 5], 'none'],
                        [[2022, 1, 10], [2022, 1, 15], 'none'],
                    ],
                    [],
                ],
                [],
            ],
        ];
    }

    /**
     * @param array<int, array<int, array{int[], int[], 'both'|'none'}>> $collectionData
     * @param array<int, array{int[], int[], 'both'|'none'}> $expected
     */
    #[DataProvider('collectionOverlapAllProvider')]
    public function testOverlapAll(array $collectionData, array $expected): void
    {
        $collections = [];

        foreach ($collectionData as $periodData) {
            $periods = [];

            foreach ($periodData as [$start, $end, $boundaries]) {
                $periods[] = new Period(
                    DateTime::createFromArray($start),
                    DateTime::createFromArray($end),
                    excludeBoundaries: $boundaries
                );
            }

            $collections[] = new PeriodCollection(...$periods);
        }

        $overlaps = $collections[0]->overlapAll(...array_slice($collections, 1));

        $this->assertCount(count($expected), $overlaps);

        foreach ($expected as $index => [$start, $end, $boundaries]) {
            $overlap = $overlaps->get($index);

            $this->assertInstanceOf(DateTime::class, $overlap->start());
            $this->assertInstanceOf(DateTime::class, $overlap->end());
            $this->assertSame(DateTime::createFromArray($start)->toIsoString(), $overlap->start()->toIsoString());
            $this->assertSame(DateTime::createFromArray($end)->toIsoString(), $overlap->end()->toIsoString());
            $this->assertSame($boundaries, Period::getBoundaries($overlap->includesStart(), $overlap->includesEnd()));
        }
    }

    /**
     * @param array<int, array<int, array{int[], int[], 'both'|'none'}>> $collectionData
     * @param array<int, array{int[], int[], 'both'|'none'}> $expected
     */
    #[DataProvider('collectionOverlapAllProvider')]
    public function testOverlapAllDate(array $collectionData, array $expected): void
    {
        $collections = [];

        foreach ($collectionData as $periodData) {
            $periods = [];

            foreach ($periodData as [$start, $end, $boundaries]) {
                $periods[] = new Period(
                    Date::createFromArray($start),
                    Date::createFromArray($end),
                    excludeBoundaries: $boundaries
                );
            }

            $collections[] = new PeriodCollection(...$periods);
        }

        $overlaps = $collections[0]->overlapAll(...array_slice($collections, 1));

        $this->assertCount(count($expected), $overlaps);

        foreach ($expected as $index => [$start, $end, $boundaries]) {
            $overlap = $overlaps->get($index);

            $this->assertInstanceOf(Date::class, $overlap->start());
            $this->assertInstanceOf(Date::class, $overlap->end());
            $this->assertSame(Date::createFromArray($start)->toIsoString(), $overlap->start()->toIsoString());
            $this->assertSame(Date::createFromArray($end)->toIsoString(), $overlap->end()->toIsoString());
            $this->assertSame($boundaries, Period::getBoundaries($overlap->includesStart(), $overlap->includesEnd()));
        }
    }
}
