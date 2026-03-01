<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Period;

use Fyre\Utility\DateTime\Period;

trait RenewTestTrait
{
    public function testRenew(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15');
        $period2 = $period1->renew();

        $this->assertInstanceOf(
            Period::class,
            $period2
        );

        $this->assertNotSame(
            $period1,
            $period2
        );

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period2->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-29T00:00:00.000+00:00',
            $period2->end()->toIsoString()
        );
    }

    public function testRenewExcludeBoth(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'both');
        $period2 = $period1->renew();

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period2->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-29T00:00:00.000+00:00',
            $period2->end()->toIsoString()
        );

        $this->assertFalse(
            $period2->includesStart()
        );

        $this->assertFalse(
            $period2->includesEnd()
        );
    }

    public function testRenewExcludeEnd(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'end');
        $period2 = $period1->renew();

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period2->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-29T00:00:00.000+00:00',
            $period2->end()->toIsoString()
        );

        $this->assertTrue(
            $period2->includesStart()
        );

        $this->assertFalse(
            $period2->includesEnd()
        );
    }

    public function testRenewExcludeStart(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15', excludeBoundaries: 'start');
        $period2 = $period1->renew();

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period2->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-29T00:00:00.000+00:00',
            $period2->end()->toIsoString()
        );

        $this->assertFalse(
            $period2->includesStart()
        );

        $this->assertTrue(
            $period2->includesEnd()
        );
    }

    public function testRenewGranularity(): void
    {
        $period1 = new Period('2022-01-01', '2022-01-15', 'hour');
        $period2 = $period1->renew();

        $this->assertSame(
            '2022-01-15T00:00:00.000+00:00',
            $period2->start()->toIsoString()
        );

        $this->assertSame(
            '2022-01-29T00:00:00.000+00:00',
            $period2->end()->toIsoString()
        );

        $this->assertSame(
            'hour',
            $period1->renew()->granularity()
        );
    }
}
