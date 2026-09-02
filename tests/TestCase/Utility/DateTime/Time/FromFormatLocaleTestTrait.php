<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Time;

use Fyre\Utility\DateTime\Time;

trait FromFormatLocaleTestTrait
{
    public function testCreateFromFormatLocale(): void
    {
        $this->assertSame(
            '23:25:01',
            Time::createFromFormat(
                'HH:mm:ss',
                '٢٣:٢٥:٠١',
                locale: 'ar-eg'
            )->toIsoString()
        );
    }
}
