<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use DateMalformedStringException;
use Fyre\Utility\DateTime\DateTime;
use PHPUnit\Framework\Attributes\DataProvider;

use function time;

trait CreateTestTrait
{
    /**
     * @return array<string, array{string, string}>
     */
    public static function constructorStringProvider(): array
    {
        return [
            'date' => ['January 1, 2019', '2019-01-01T00:00:00.000+00:00'],
            'date time' => ['January 1, 2019 00:00:00', '2019-01-01T00:00:00.000+00:00'],
            'iso' => ['2019-01-01T00:00:00', '2019-01-01T00:00:00.000+00:00'],
            'milliseconds' => ['2019-01-01 00:00:00.123', '2019-01-01T00:00:00.123+00:00'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function constructorTimeZoneProvider(): array
    {
        return [
            'name' => ['Australia/Brisbane'],
            'offset' => ['+10:00'],
            'offset without colon' => ['+1000'],
        ];
    }

    /**
     * @return array<string, array{int[], string}>
     */
    public static function createFromArrayProvider(): array
    {
        return [
            'year' => [
                [2019],
                '2019-01-01T00:00:00.000+00:00',
            ],
            'date' => [
                [2019, 1, 2],
                '2019-01-02T00:00:00.000+00:00',
            ],
            'hour' => [
                [2019, 1, 1, 1],
                '2019-01-01T01:00:00.000+00:00',
            ],
            'millisecond' => [
                [2019, 1, 1, 0, 0, 0, 1],
                '2019-01-01T00:00:00.001+00:00',
            ],
            'minute' => [
                [2019, 1, 1, 0, 1],
                '2019-01-01T00:01:00.000+00:00',
            ],
            'month' => [
                [2019, 2],
                '2019-02-01T00:00:00.000+00:00',
            ],
            'second' => [
                [2019, 1, 1, 0, 0, 1],
                '2019-01-01T00:00:01.000+00:00',
            ],
        ];
    }

    public function testConstructor(): void
    {
        $start = time();
        $now = new DateTime()->getTimestamp();
        $end = time();

        $this->assertGreaterThanOrEqual(
            $start,
            $now
        );

        $this->assertLessThanOrEqual(
            $end,
            $now
        );
    }

    #[DataProvider('constructorStringProvider')]
    public function testConstructorString(string $value, string $expected): void
    {
        $this->assertSame(
            $expected,
            new DateTime($value)->toIsoString()
        );
    }

    public function testConstructorWithLocale(): void
    {
        $this->assertSame(
            'الثلاثاء يناير ٠١ ٢٠١٩ ٠٠:٠٠:٠٠ +0000 (UTC)',
            new DateTime('January 1, 2019 00:00:00', locale: 'ar-eg')->toString()
        );
    }

    #[DataProvider('constructorTimeZoneProvider')]
    public function testConstructorWithTimeZone(string $timeZone): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            new DateTime('January 1, 2019 00:00:00', $timeZone)->toIsoString()
        );
    }

    /**
     * @param int[] $parts
     */
    #[DataProvider('createFromArrayProvider')]
    public function testCreateFromArray(array $parts, string $expected): void
    {
        $this->assertSame(
            $expected,
            DateTime::createFromArray($parts)->toIsoString()
        );
    }

    public function testCreateFromArrayInstanceOf(): void
    {
        $this->assertInstanceOf(
            DateTime::class,
            DateTime::createFromArray([2018])
        );
    }

    public function testCreateFromArrayWithLocale(): void
    {
        $this->assertSame(
            'الثلاثاء يناير ٠١ ٢٠١٩ ٠٠:٠٠:٠٠ +0000 (UTC)',
            DateTime::createFromArray([2019, 1, 1, 0, 0, 0], locale: 'ar-eg')->toString()
        );
    }

    public function testCreateFromArrayWithTimeZone(): void
    {
        $this->assertSame(
            'Tue Jan 01 2019 00:00:00 +1000 (Australia/Brisbane)',
            DateTime::createFromArray([2019, 1, 1, 0, 0, 0], 'Australia/Brisbane')->toString()
        );
    }

    public function testCreateFromIsoString(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.123+00:00',
            DateTime::createFromIsoString('2019-01-01T00:00:00.123+00:00')->toIsoString()
        );
    }

    public function testCreateFromIsoStringInstanceOf(): void
    {
        $this->assertInstanceOf(
            DateTime::class,
            DateTime::createFromIsoString('2019-01-01T00:00:00.000+00:00')
        );
    }

    public function testCreateFromIsoStringInvalid(): void
    {
        $this->expectException(DateMalformedStringException::class);
        $this->expectExceptionMessageIs('Date string is not valid RFC 3339.');

        DateTime::createFromIsoString('invalid');
    }

    public function testCreateFromIsoStringWithLocale(): void
    {
        $this->assertSame(
            'الثلاثاء يناير ٠١ ٢٠١٩ ٠٠:٠٠:٠٠ +0000 (UTC)',
            DateTime::createFromIsoString('2019-01-01T00:00:00.000+00:00', locale: 'ar-eg')->toString()
        );
    }

    public function testCreateFromIsoStringWithTimeZone(): void
    {
        $this->assertSame(
            'Tue Jan 01 2019 10:00:00 +1000 (Australia/Brisbane)',
            DateTime::createFromIsoString('2019-01-01T00:00:00.000+00:00', 'Australia/Brisbane')->toString()
        );
    }

    public function testCreateFromNativeDateTime(): void
    {
        $date = new \DateTime('@1546300800');
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromNativeDateTime($date)->toIsoString()
        );
    }

    public function testCreateFromNativeDateTimeInstanceOf(): void
    {
        $date = new \DateTime('@1546300800');
        $this->assertInstanceOf(
            DateTime::class,
            DateTime::createFromNativeDateTime($date)
        );
    }

    public function testCreateFromNativeDateTimeMilliseconds(): void
    {
        $date = new \DateTime('2019-01-01 00:00:00.123');
        $this->assertSame(
            '2019-01-01T00:00:00.123+00:00',
            DateTime::createFromNativeDateTime($date)->toIsoString()
        );
    }

    public function testCreateFromNativeDateTimeWithLocale(): void
    {
        $date = new \DateTime('@1546300800');
        $this->assertSame(
            'الثلاثاء يناير ٠١ ٢٠١٩ ٠٠:٠٠:٠٠ +0000 (GMT)',
            DateTime::createFromNativeDateTime($date, locale: 'ar-eg')->toString()
        );
    }

    public function testCreateFromNativeDateTimeWithTimeZone(): void
    {
        $date = new \DateTime('@1546300800');
        $this->assertSame(
            'Tue Jan 01 2019 10:00:00 +1000 (Australia/Brisbane)',
            DateTime::createFromNativeDateTime($date, 'Australia/Brisbane')->toString()
        );
    }

    public function testCreateFromTimestamp(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromTimestamp(1546300800)->toIsoString()
        );
    }

    public function testCreateFromTimestampInstanceOf(): void
    {
        $this->assertInstanceOf(
            DateTime::class,
            DateTime::createFromTimestamp(1546300800)
        );
    }

    public function testCreateFromTimestampWithLocale(): void
    {
        $this->assertSame(
            'الثلاثاء يناير ٠١ ٢٠١٩ ٠٠:٠٠:٠٠ +0000 (UTC)',
            DateTime::createFromTimestamp(1546300800, locale: 'ar-eg')->toString()
        );
    }

    public function testCreateFromTimestampWithTimeZone(): void
    {
        $this->assertSame(
            'Tue Jan 01 2019 10:00:00 +1000 (Australia/Brisbane)',
            DateTime::createFromTimestamp(1546300800, 'Australia/Brisbane')->toString()
        );
    }

    public function testNow(): void
    {
        $start = time();
        $now = DateTime::now()->getTimestamp();
        $end = time();

        $this->assertGreaterThanOrEqual(
            $start,
            $now
        );

        $this->assertLessThanOrEqual(
            $end,
            $now
        );
    }

    public function testNowInstanceOf(): void
    {
        $this->assertInstanceOf(
            DateTime::class,
            DateTime::now()
        );
    }

    public function testNowWithLocale(): void
    {
        $this->assertSame(
            'ar-eg',
            DateTime::now(locale: 'ar-eg')->getLocale()
        );
    }

    public function testNowWithTimeZone(): void
    {
        $this->assertSame(
            'Australia/Brisbane',
            DateTime::now('Australia/Brisbane')->getTimeZone()
        );
    }
}
