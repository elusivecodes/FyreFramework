<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Date;

use Fyre\Utility\DateTime\Date;

trait FromFormatLocaleTestTrait
{
    public function testCreateFromFormatLocale(): void
    {
        $this->assertSame(
            '2019-01-21',
            Date::createFromFormat(
                'dd/MM/yyyy',
                '٢١/٠١/٢٠١٩',
                locale: 'ar-eg'
            )->toIsoString()
        );
    }
}
