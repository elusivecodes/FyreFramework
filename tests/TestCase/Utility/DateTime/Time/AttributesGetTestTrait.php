<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Time;

use Fyre\Utility\DateTime\Time;
use PHPUnit\Framework\Attributes\DataProvider;

trait AttributesGetTestTrait
{
    /**
     * @return array<string, array{int[], string, int|string}>
     */
    public static function attributeProvider(): array
    {
        return [
            'hours' => [[6], 'getHours', 6],
            'locale' => [[0], 'getLocale', 'en'],
            'milliseconds' => [[0, 0, 0, 550], 'getMilliseconds', 550],
            'minutes' => [[0, 32], 'getMinutes', 32],
            'seconds' => [[0, 0, 25], 'getSeconds', 25],
            'time' => [[12, 30], 'getTime', 45000000],
            'timestamp' => [[12, 30], 'getTimestamp', 45000],
        ];
    }

    /**
     * @param int[] $parts
     */
    #[DataProvider('attributeProvider')]
    public function testGetAttribute(array $parts, string $method, int|string $expected): void
    {
        $time = Time::createFromArray($parts);

        $this->assertSame(
            $expected,
            $time->{$method}()
        );
    }

    public function testGetTimeZone(): void
    {
        $this->assertSame(
            'UTC',
            Time::now('Australia/Brisbane')->getTimeZone()
        );
    }
}
