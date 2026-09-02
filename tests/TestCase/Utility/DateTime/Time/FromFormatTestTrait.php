<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Time;

use DateMalformedStringException;
use Fyre\Utility\DateTime\Time;

trait FromFormatTestTrait
{
    public function testCreateFromFormat(): void
    {
        $this->assertSame(
            '12:30:15',
            Time::createFromFormat(
                'HH:mm:ss',
                '12:30:15',
                'Australia/Brisbane'
            )->toIsoString()
        );
    }

    public function testCreateFromFormatInvalid(): void
    {
        $this->expectException(DateMalformedStringException::class);
        $this->expectExceptionMessageIsOrContains('Date parsing failed: U_PARSE_ERROR');
        $this->expectExceptionCode(9);

        Time::createFromFormat('yyyy', 'a');
    }
}
