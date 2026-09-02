<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Date;

use Fyre\Utility\DateTime\Date;

trait OutputTestTrait
{
    public function testAsString(): void
    {
        $this->assertSame(
            'Tue Jan 01 2019',
            ''.Date::createFromArray([2019, 1, 1])
        );
    }

    public function testToIsoStringWithLocale(): void
    {
        $this->assertSame(
            '2019-01-01',
            Date::createFromArray([2019, 1, 1], locale: 'ar-eg')->toIsoString()
        );
    }

    public function testToNativeDateTime(): void
    {
        $this->assertSame(
            '2019-01-01 00:00:00.000 UTC',
            Date::createFromArray([2019, 1, 1])
                ->toNativeDateTime()
                ->format('Y-m-d H:i:s.v e')
        );
    }

    public function testToString(): void
    {
        $this->assertSame(
            'Tue Jan 01 2019',
            Date::createFromArray([2019, 1, 1])->toString()
        );
    }
}
