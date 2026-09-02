<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Date;

use Fyre\Utility\DateTime\Date;
use PHPUnit\Framework\Attributes\DataProvider;

trait FormatLocaleTestTrait
{
    /**
     * @return array<string, array{int[], string, string, string}>
     */
    public static function formatLocaleProvider(): array
    {
        return [
            'localized date' => [[2019, 1, 21], 'ar-eg', 'yyyy-MM-dd', '٢٠١٩-٠١-٢١'],
            'localized month' => [[2019, 10, 1], 'ru', 'LLLL', 'октябрь'],
        ];
    }

    /**
     * @param int[] $parts
     */
    #[DataProvider('formatLocaleProvider')]
    public function testFormatLocale(array $parts, string $locale, string $format, string $expected): void
    {
        $this->assertSame(
            $expected,
            Date::createFromArray($parts, locale: $locale)->format($format)
        );
    }
}
