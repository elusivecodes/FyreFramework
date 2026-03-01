<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;

use function time;

trait CreateTestTrait
{
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

    public function testConstructorDate(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            new DateTime('January 1, 2019')->toIsoString()
        );
    }

    public function testConstructorDateTime(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            new DateTime('January 1, 2019 00:00:00')->toIsoString()
        );
    }

    public function testConstructorIso(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            new DateTime('2019-01-01T00:00:00')->toIsoString()
        );
    }

    public function testConstructorMilliseconds(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.123+00:00',
            new DateTime('2019-01-01 00:00:00.123')->toIsoString()
        );
    }

    public function testConstructorWithLocale(): void
    {
        $this->assertSame(
            'الثلاثاء يناير ٠١ ٢٠١٩ ٠٠:٠٠:٠٠ +0000 (UTC)',
            new DateTime('January 1, 2019 00:00:00', null, 'ar-eg')->toString()
        );
    }

    public function testConstructorWithTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            new DateTime('January 1, 2019 00:00:00', 'Australia/Brisbane')->toIsoString()
        );
    }

    public function testConstructorWithTimeZoneFromOffset(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            new DateTime('January 1, 2019 00:00:00', '+10:00')->toIsoString()
        );
    }

    public function testConstructorWithTimeZoneFromOffsetWithoutColon(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            new DateTime('January 1, 2019 00:00:00', '+1000')->toIsoString()
        );
    }

    public function testCreateFromArray(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromArray([2019])->toIsoString()
        );
    }

    public function testCreateFromArrayDate(): void
    {
        $this->assertSame(
            '2019-01-02T00:00:00.000+00:00',
            DateTime::createFromArray([2019, 1, 2])->toIsoString()
        );
    }

    public function testCreateFromArrayHour(): void
    {
        $this->assertSame(
            '2019-01-01T01:00:00.000+00:00',
            DateTime::createFromArray([2019, 1, 1, 1])->toIsoString()
        );
    }

    public function testCreateFromArrayInstanceOf(): void
    {
        $this->assertInstanceOf(
            DateTime::class,
            DateTime::createFromArray([2018])
        );
    }

    public function testCreateFromArrayMillisecond(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.001+00:00',
            DateTime::createFromArray([2019, 1, 1, 0, 0, 0, 1])->toIsoString()
        );
    }

    public function testCreateFromArrayMinute(): void
    {
        $this->assertSame(
            '2019-01-01T00:01:00.000+00:00',
            DateTime::createFromArray([2019, 1, 1, 0, 1])->toIsoString()
        );
    }

    public function testCreateFromArrayMonth(): void
    {
        $this->assertSame(
            '2019-02-01T00:00:00.000+00:00',
            DateTime::createFromArray([2019, 2])->toIsoString()
        );
    }

    public function testCreateFromArraySecond(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:01.000+00:00',
            DateTime::createFromArray([2019, 1, 1, 0, 0, 1])->toIsoString()
        );
    }

    public function testCreateFromArrayWithLocale(): void
    {
        $this->assertSame(
            'الثلاثاء يناير ٠١ ٢٠١٩ ٠٠:٠٠:٠٠ +0000 (UTC)',
            DateTime::createFromArray([2019, 1, 1, 0, 0, 0], null, 'ar-eg')->toString()
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
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromIsoString('2019-01-01T00:00:00.000+00:00')->toIsoString()
        );
    }

    public function testCreateFromIsoStringInstanceOf(): void
    {
        $this->assertInstanceOf(
            DateTime::class,
            DateTime::createFromIsoString('2019-01-01T00:00:00.000+00:00')
        );
    }

    public function testCreateFromIsoStringWithLocale(): void
    {
        $this->assertSame(
            'الثلاثاء يناير ٠١ ٢٠١٩ ٠٠:٠٠:٠٠ +0000 (UTC)',
            DateTime::createFromIsoString('2019-01-01T00:00:00.000+00:00', null, 'ar-eg')->toString()
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
            DateTime::createFromNativeDateTime($date, null, 'ar-eg')->toString()
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
            DateTime::createFromTimestamp(1546300800, null, 'ar-eg')->toString()
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
            DateTime::now(null, 'ar-eg')->getLocale()
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
