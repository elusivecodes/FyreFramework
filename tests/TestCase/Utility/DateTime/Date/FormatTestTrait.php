<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Date;

use Fyre\Utility\DateTime\Date;
use PHPUnit\Framework\Attributes\DataProvider;

trait FormatTestTrait
{
    /**
     * @return array<string, array{int[], string, string}>
     */
    public static function formatProvider(): array
    {
        return [
            'date' => [[2018, 10, 21], 'yyyy-MM-dd', '2018-10-21'],
            'quarter' => [[2018, 8, 1], 'q', '3'],
            'week day' => [[2018, 6, 1], 'eeee', 'Friday'],
        ];
    }

    /**
     * @param int[] $parts
     */
    #[DataProvider('formatProvider')]
    public function testFormat(array $parts, string $format, string $expected): void
    {
        $this->assertSame(
            $expected,
            Date::createFromArray($parts)->format($format)
        );
    }

    public function testFormatTimeZone(): void
    {
        $this->assertSame(
            'UTC',
            Date::now('Australia/Brisbane')->format('VV')
        );
    }
}
