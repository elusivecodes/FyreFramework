<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Date;

use DateMalformedStringException;
use Fyre\Utility\DateTime\Date;

trait FromFormatTestTrait
{
    public function testCreateFromFormat(): void
    {
        $this->assertSame(
            '2019-02-01',
            Date::createFromFormat(
                'dd/MM/yyyy',
                '01/02/2019',
                'Australia/Brisbane'
            )->toIsoString()
        );
    }

    public function testCreateFromFormatInvalid(): void
    {
        $this->expectException(DateMalformedStringException::class);
        $this->expectExceptionMessageIsOrContains('Date parsing failed: U_PARSE_ERROR');
        $this->expectExceptionCode(9);

        Date::createFromFormat('yyyy', 'a');
    }
}
