<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Time;

use Fyre\Utility\DateTime\Time;
use PHPUnit\Framework\Attributes\DataProvider;

trait FormatLocaleTestTrait
{
    /**
     * @return array<string, array{int[], string, string, string}>
     */
    public static function formatLocaleProvider(): array
    {
        return [
            'localized day period' => [[12], 'zh', 'aaaa', '下午'],
            'localized time' => [[23, 25, 1], 'ar-eg', 'HH:mm:ss', '٢٣:٢٥:٠١'],
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
            Time::createFromArray($parts, locale: $locale)->format($format)
        );
    }
}
