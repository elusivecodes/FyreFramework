<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Time;

use Fyre\Utility\DateTime\Time;

trait OutputTestTrait
{
    public function testAsString(): void
    {
        $this->assertSame(
            '12:30:15.123',
            ''.Time::createFromArray([12, 30, 15, 123])
        );
    }

    public function testToIsoStringWithLocale(): void
    {
        $this->assertSame(
            '12:30:15.123',
            Time::createFromArray([12, 30, 15, 123], locale: 'ar-eg')->toIsoString()
        );
    }

    public function testToNativeDateTime(): void
    {
        $this->assertSame(
            '1970-01-01 12:30:15.123 UTC',
            Time::createFromArray([12, 30, 15, 123])
                ->toNativeDateTime()
                ->format('Y-m-d H:i:s.v e')
        );
    }

    public function testToString(): void
    {
        $this->assertSame(
            '12:30:15.123',
            Time::createFromArray([12, 30, 15, 123])->toString()
        );
    }
}
