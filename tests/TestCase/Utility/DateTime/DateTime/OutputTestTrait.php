<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use DateTimeInterface;
use Fyre\Utility\DateTime\DateTime;

trait OutputTestTrait
{
    public function testAsString(): void
    {
        $this->assertSame(
            'Mon Jan 01 2018 00:00:00 +1000 (Australia/Brisbane)',
            ''.DateTime::createFromArray([2018], 'Australia/Brisbane')
        );
    }

    public function testToDateString(): void
    {
        $this->assertSame(
            'Mon Jan 01 2018',
            DateTime::createFromArray([2018])->toDateString()
        );
    }

    public function testToIsoString(): void
    {
        $this->assertSame(
            '2017-12-31T14:00:00.123+00:00',
            DateTime::createFromArray([2018, 1, 1, 0, 0, 0, 123], 'Australia/Brisbane')->toIsoString()
        );
    }

    public function testToNativeDateTime(): void
    {
        $this->assertSame(
            '2018-01-01T00:00:00.123+10:00',
            DateTime::createFromArray([2018, 1, 1, 0, 0, 0, 123], 'Australia/Brisbane')
                ->toNativeDateTime()
                ->format(DateTimeInterface::RFC3339_EXTENDED)
        );
    }

    public function testToString(): void
    {
        $this->assertSame(
            'Mon Jan 01 2018 00:00:00 +1000 (Australia/Brisbane)',
            DateTime::createFromArray([2018], 'Australia/Brisbane')->toString()
        );
    }

    public function testToTimeString(): void
    {
        $this->assertSame(
            '00:00:00 +0000 (UTC)',
            DateTime::createFromArray([2018])->toTimeString()
        );
    }

    public function testToUtcString(): void
    {
        $this->assertSame(
            'Sun Dec 31 2017 14:00:00 +0000 (UTC)',
            DateTime::createFromArray([2018], 'Australia/Brisbane')->toUTCString()
        );
    }
}
