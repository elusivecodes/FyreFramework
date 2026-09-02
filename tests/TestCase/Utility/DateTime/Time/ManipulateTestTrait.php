<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Time;

use Fyre\Utility\DateTime\Time;
use PHPUnit\Framework\Attributes\DataProvider;

trait ManipulateTestTrait
{
    /**
     * @return array<string, array{int[], string, int[], string}>
     */
    public static function manipulateProvider(): array
    {
        return [
            'add hour' => [[0], 'addHour', [], '01:00:00'],
            'add hours' => [[0], 'addHours', [2], '02:00:00'],
            'add minute' => [[0], 'addMinute', [], '00:01:00'],
            'add minutes' => [[0], 'addMinutes', [2], '00:02:00'],
            'add second' => [[23, 59, 59, 999], 'addSecond', [], '00:00:00.999'],
            'add seconds' => [[0], 'addSeconds', [2], '00:00:02'],
            'end of hour' => [[11, 30, 30, 500], 'endOfHour', [], '11:59:59.999'],
            'end of minute' => [[11, 30, 30, 500], 'endOfMinute', [], '11:30:59.999'],
            'end of second' => [[11, 30, 30, 500], 'endOfSecond', [], '11:30:30.999'],
            'start of hour' => [[11, 30, 30, 500], 'startOfHour', [], '11:00:00'],
            'start of minute' => [[11, 30, 30, 500], 'startOfMinute', [], '11:30:00'],
            'start of second' => [[11, 30, 30, 500], 'startOfSecond', [], '11:30:30'],
            'sub hour' => [[0], 'subHour', [], '23:00:00'],
            'sub hours' => [[0], 'subHours', [2], '22:00:00'],
            'sub minute' => [[0], 'subMinute', [], '23:59:00'],
            'sub minutes' => [[0], 'subMinutes', [2], '23:58:00'],
            'sub second' => [[0], 'subSecond', [], '23:59:59'],
            'sub seconds' => [[0], 'subSeconds', [2], '23:59:58'],
        ];
    }

    /**
     * @param int[] $parts
     * @param int[] $arguments
     */
    #[DataProvider('manipulateProvider')]
    public function testManipulate(array $parts, string $method, array $arguments, string $expected): void
    {
        $time1 = Time::createFromArray($parts);

        /** @var Time $time2 */
        $time2 = $time1->$method(...$arguments);

        $this->assertNotSame(
            $time1,
            $time2
        );
        $this->assertSame(
            $expected,
            $time2->toIsoString()
        );
    }
}
