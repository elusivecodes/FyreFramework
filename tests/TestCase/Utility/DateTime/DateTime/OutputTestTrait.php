<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

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
            '2017-12-31T14:00:00.000+00:00',
            DateTime::createFromArray([2018], 'Australia/Brisbane')->toIsoString()
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
