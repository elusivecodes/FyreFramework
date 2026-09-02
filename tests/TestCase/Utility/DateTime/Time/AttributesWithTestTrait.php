<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Time;

use Fyre\Utility\DateTime\Time;
use PHPUnit\Framework\Attributes\DataProvider;

trait AttributesWithTestTrait
{
    /**
     * @return array<string, array{int[], string, int[], string}>
     */
    public static function transformationProvider(): array
    {
        return [
            'hours with milliseconds' => [[0], 'withHours', [0, 0, 0, 303], '00:00:00.303'],
            'milliseconds' => [[0], 'withMilliseconds', [220], '00:00:00.220'],
            'minutes with milliseconds' => [[0], 'withMinutes', [0, 0, 320], '00:00:00.320'],
            'seconds with milliseconds' => [[0], 'withSeconds', [0, 550], '00:00:00.550'],
        ];
    }

    /**
     * @param int[] $parts
     * @param int[] $arguments
     */
    #[DataProvider('transformationProvider')]
    public function testTransformation(array $parts, string $method, array $arguments, string $expected): void
    {
        $time1 = Time::createFromArray($parts);

        /** @var Time $time2 */
        $time2 = $time1->{$method}(...$arguments);

        $this->assertNotSame(
            $time1,
            $time2
        );
        $this->assertSame(
            $expected,
            $time2->toIsoString()
        );
    }

    public function testWithLocale(): void
    {
        $time = Time::createFromArray([12, 30, 15, 123]);
        $result = $time->withLocale('ar-eg');

        $this->assertNotSame(
            $time,
            $result
        );
        $this->assertArraysAreIdentical(
            ['12:30:15.123', 'UTC', 'ar-eg'],
            [$result->toIsoString(), $result->getTimeZone(), $result->getLocale()]
        );
    }
}
