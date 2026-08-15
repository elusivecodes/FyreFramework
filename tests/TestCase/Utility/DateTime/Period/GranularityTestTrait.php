<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use PHPUnit\Framework\Attributes\DataProvider;

trait GranularityTestTrait
{
    /**
     * @return array<string, mixed[]>
     */
    public static function granularityProvider(): array
    {
        return [
            'year' => ['2020-01-01', '2022-01-01', '2021-01-01T00:00:00.000+00:00', 'year'],
            'month' => ['2022-01-01', '2022-03-01', '2022-02-01T00:00:00.000+00:00', 'month'],
            'day' => ['2022-01-01', '2022-01-03', '2022-01-02T00:00:00.000+00:00', 'day'],
            'hour' => ['2022-01-01 00:00:00', '2022-01-01 02:00:00', '2022-01-01T01:00:00.000+00:00', 'hour'],
            'minute' => ['2022-01-01 00:00:00', '2022-01-01 00:02:00', '2022-01-01T00:01:00.000+00:00', 'minute'],
            'second' => ['2022-01-01 00:00:00', '2022-01-01 00:00:02', '2022-01-01T00:00:01.000+00:00', 'second'],
        ];
    }

    /**
     * @param 'day'|'hour'|'minute'|'month'|'second'|'year' $granularity
     */
    #[DataProvider('granularityProvider')]
    public function testGranularityOperations(string $start, string $end, string $expected, string $granularity): void
    {
        $period = new Period($start, $end, $granularity, 'both');
        $included = new DateTime($expected);

        $this->assertSame(1, $period->count());
        $this->assertSame($expected, $period->current()->toIsoString());
        $this->assertTrue($period->startEquals($included));
        $this->assertTrue($period->startsAfter(new DateTime($start)));
        $this->assertTrue($period->startsAfterOrEquals($included));
        $this->assertTrue($period->endsBefore(new DateTime($end)));
        $this->assertTrue($period->endsBeforeOrEquals($included));
    }
}
