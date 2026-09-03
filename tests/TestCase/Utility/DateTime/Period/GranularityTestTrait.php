<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use PHPUnit\Framework\Attributes\DataProvider;

trait GranularityTestTrait
{
    /**
     * @return array<string, array{int[], int[], int[], 'day'|'month'|'year'}>
     */
    public static function dateGranularityProvider(): array
    {
        return [
            'year' => [
                [2020, 1, 1],
                [2022, 1, 1],
                [2021, 1, 1],
                'year',
            ],
            'month' => [
                [2022, 1, 1],
                [2022, 3, 1],
                [2022, 2, 1],
                'month',
            ],
            'day' => [
                [2022, 1, 1],
                [2022, 1, 3],
                [2022, 1, 2],
                'day',
            ],
        ];
    }

    /**
     * @return array<string, array{int[], int[], int[], 'day'|'hour'|'minute'|'month'|'second'|'year'}>
     */
    public static function granularityProvider(): array
    {
        return [
            'year' => [
                [2020, 1, 1],
                [2022, 1, 1],
                [2021, 1, 1],
                'year',
            ],
            'month' => [
                [2022, 1, 1],
                [2022, 3, 1],
                [2022, 2, 1],
                'month',
            ],
            'day' => [
                [2022, 1, 1],
                [2022, 1, 3],
                [2022, 1, 2],
                'day',
            ],
            'hour' => [
                [
                    2022,
                    1,
                    1,
                    0,
                    0,
                    0,
                ],
                [
                    2022,
                    1,
                    1,
                    2,
                    0,
                    0,
                ],
                [
                    2022,
                    1,
                    1,
                    1,
                    0,
                    0,
                ],
                'hour',
            ],
            'minute' => [
                [
                    2022,
                    1,
                    1,
                    0,
                    0,
                    0,
                ],
                [
                    2022,
                    1,
                    1,
                    0,
                    2,
                    0,
                ],
                [
                    2022,
                    1,
                    1,
                    0,
                    1,
                    0,
                ],
                'minute',
            ],
            'second' => [
                [
                    2022,
                    1,
                    1,
                    0,
                    0,
                    0,
                ],
                [
                    2022,
                    1,
                    1,
                    0,
                    0,
                    2,
                ],
                [
                    2022,
                    1,
                    1,
                    0,
                    0,
                    1,
                ],
                'second',
            ],
        ];
    }

    /**
     * @param int[] $start
     * @param int[] $end
     * @param int[] $expected
     * @param 'day'|'month'|'year' $granularity
     */
    #[DataProvider('dateGranularityProvider')]
    public function testDateGranularityOperations(array $start, array $end, array $expected, string $granularity): void
    {
        $period = new Period(
            Date::createFromArray($start),
            Date::createFromArray($end),
            $granularity,
            'both'
        );
        $included = Date::createFromArray($expected);

        $this->assertSame(1, $period->count());
        $this->assertSame($included->toIsoString(), $period->current()->toIsoString());
        $this->assertTrue($period->startEquals($included));
        $this->assertTrue($period->startsAfter(Date::createFromArray($start)));
        $this->assertTrue($period->startsAfterOrEquals($included));
        $this->assertTrue($period->endsBefore(Date::createFromArray($end)));
        $this->assertTrue($period->endsBeforeOrEquals($included));
    }

    /**
     * @param int[] $start
     * @param int[] $end
     * @param int[] $expected
     * @param 'day'|'hour'|'minute'|'month'|'second'|'year' $granularity
     */
    #[DataProvider('granularityProvider')]
    public function testGranularityOperations(array $start, array $end, array $expected, string $granularity): void
    {
        $period = new Period(
            DateTime::createFromArray($start),
            DateTime::createFromArray($end),
            $granularity,
            'both'
        );
        $included = DateTime::createFromArray($expected);

        $this->assertSame(1, $period->count());
        $this->assertSame($included->toIsoString(), $period->current()->toIsoString());
        $this->assertTrue($period->startEquals($included));
        $this->assertTrue($period->startsAfter(DateTime::createFromArray($start)));
        $this->assertTrue($period->startsAfterOrEquals($included));
        $this->assertTrue($period->endsBefore(DateTime::createFromArray($end)));
        $this->assertTrue($period->endsBeforeOrEquals($included));
    }
}
