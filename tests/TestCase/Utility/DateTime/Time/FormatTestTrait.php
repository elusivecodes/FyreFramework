<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Time;

use Fyre\Utility\DateTime\Time;
use PHPUnit\Framework\Attributes\DataProvider;

trait FormatTestTrait
{
    /**
     * @return array<string, array{int[], string, string}>
     */
    public static function formatProvider(): array
    {
        return [
            'day period' => [[12], 'aaaa', 'PM'],
            'fractional' => [[0, 0, 0, 123], 'SSS', '123'],
            'time' => [[23, 25, 1], 'HH:mm:ss', '23:25:01'],
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
            Time::createFromArray($parts)->format($format)
        );
    }

    public function testFormatTimeZone(): void
    {
        $this->assertSame(
            'UTC',
            Time::now('Australia/Brisbane')->format('VV')
        );
    }
}
