<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Period;
use PHPUnit\Framework\Attributes\DataProvider;

trait RenewTestTrait
{
    /**
     * @return array<string, array{'both'|'end'|'none'|'start'}>
     */
    public static function renewProvider(): array
    {
        return [
            'include both' => ['none'],
            'exclude both' => ['both'],
            'exclude end' => ['end'],
            'exclude start' => ['start'],
        ];
    }

    /**
     * @param 'both'|'end'|'none'|'start' $excludeBoundaries
     */
    #[DataProvider('renewProvider')]
    public function testRenew(string $excludeBoundaries): void
    {
        $period = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $renewed = $period->renew();

        $this->assertNotSame($period, $renewed);
        $this->assertInstanceOf(DateTime::class, $renewed->start());
        $this->assertInstanceOf(DateTime::class, $renewed->end());
        $this->assertSame('2022-01-15T00:00:00.000+00:00', $renewed->start()->toIsoString());
        $this->assertSame('2022-01-29T00:00:00.000+00:00', $renewed->end()->toIsoString());
        $this->assertSame($period->includesStart(), $renewed->includesStart());
        $this->assertSame($period->includesEnd(), $renewed->includesEnd());
    }

    /**
     * @param 'both'|'end'|'none'|'start' $excludeBoundaries
     */
    #[DataProvider('renewProvider')]
    public function testRenewDate(string $excludeBoundaries): void
    {
        $period = new Period(
            Date::createFromArray([2022, 1, 1]),
            Date::createFromArray([2022, 1, 15]),
            excludeBoundaries: $excludeBoundaries
        );

        $renewed = $period->renew();

        $this->assertNotSame($period, $renewed);
        $this->assertInstanceOf(Date::class, $renewed->start());
        $this->assertInstanceOf(Date::class, $renewed->end());
        $this->assertSame('2022-01-15', $renewed->start()->toIsoString());
        $this->assertSame('2022-01-29', $renewed->end()->toIsoString());
        $this->assertSame($period->includesStart(), $renewed->includesStart());
        $this->assertSame($period->includesEnd(), $renewed->includesEnd());
    }

    public function testRenewGranularity(): void
    {
        $period = new Period(
            DateTime::createFromArray([2022, 1, 1]),
            DateTime::createFromArray([2022, 1, 15]),
            'hour'
        );

        $this->assertSame('hour', $period->renew()->granularity());
    }
}
