<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Time;

use Fyre\Utility\DateTime\Time;

trait UtilityTestTrait
{
    public function testDayPeriod(): void
    {
        $this->assertSame(
            'AM',
            Time::createFromArray([0])->dayPeriod()
        );
    }

    public function testDayPeriodPm(): void
    {
        $this->assertSame(
            'PM',
            Time::createFromArray([12])->dayPeriod()
        );
    }

    public function testDayPeriodShort(): void
    {
        $this->assertSame(
            'AM',
            Time::createFromArray([0])->dayPeriod('short')
        );
    }

    public function testDayPeriodShortPm(): void
    {
        $this->assertSame(
            'PM',
            Time::createFromArray([12])->dayPeriod('short')
        );
    }
}
