<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Time;

use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use Fyre\Utility\DateTime\Time;
use PHPUnit\Framework\Attributes\DataProvider;

trait CreateTestTrait
{
    /**
     * @return array<string, array{string}>
     */
    public static function invalidIsoStringProvider(): array
    {
        return [
            'date time' => ['2019-01-01T12:30:15'],
            'excess precision' => ['12:30:15.1234'],
            'invalid' => ['invalid'],
            'missing seconds' => ['12:30'],
            'overflow' => ['24:00:00'],
        ];
    }

    public function testConstructor(): void
    {
        $time = new Time('January 2, 2019 12:30:15.123');

        $this->assertArraysAreIdentical(
            [12, 30, 15, 123],
            [
                $time->getHours(),
                $time->getMinutes(),
                $time->getSeconds(),
                $time->getMilliseconds(),
            ]
        );
        $this->assertSame(
            '1970-01-01 12:30:15.123 UTC',
            $time->toNativeDateTime()->format('Y-m-d H:i:s.v e')
        );
    }

    public function testConstructorWithTimeZone(): void
    {
        $time = new Time(
            'January 2, 2019 12:30:15.123',
            'Australia/Brisbane'
        );

        $this->assertSame(
            '1970-01-01 12:30:15.123 UTC',
            $time->toNativeDateTime()->format('Y-m-d H:i:s.v e')
        );
    }

    public function testCreateFromArray(): void
    {
        $this->assertSame(
            '12:30:00',
            Time::createFromArray([12, 30])->toIsoString()
        );
    }

    public function testCreateFromArrayWithLocale(): void
    {
        $this->assertSame(
            'ar-eg',
            Time::createFromArray([12, 30, 15, 123], locale: 'ar-eg')->getLocale()
        );
    }

    public function testCreateFromArrayWithTimeZone(): void
    {
        $this->assertSame(
            '1970-01-01 12:30:15.123 UTC',
            Time::createFromArray([12, 30, 15, 123], 'Australia/Brisbane')
                ->toNativeDateTime()
                ->format('Y-m-d H:i:s.v e')
        );
    }

    public function testCreateFromIsoString(): void
    {
        $this->assertSame(
            '12:30:15',
            Time::createFromIsoString('12:30:15')->toIsoString()
        );
    }

    #[DataProvider('invalidIsoStringProvider')]
    public function testCreateFromIsoStringInvalid(string $value): void
    {
        $this->expectException(DateMalformedStringException::class);
        $this->expectExceptionMessageIs('Time string is not valid ISO 8601.');

        Time::createFromIsoString($value);
    }

    public function testCreateFromIsoStringMilliseconds(): void
    {
        $this->assertSame(
            '12:30:15.123',
            Time::createFromIsoString('12:30:15.123')->toIsoString()
        );
    }

    public function testCreateFromIsoStringRoundTrip(): void
    {
        $time = Time::createFromArray([12, 30, 15, 123]);

        $this->assertSame(
            $time->toIsoString(),
            Time::createFromIsoString($time->toIsoString())->toIsoString()
        );
    }

    public function testCreateFromIsoStringWithLocale(): void
    {
        $this->assertSame(
            'ar-eg',
            Time::createFromIsoString('12:30:15.123', locale: 'ar-eg')->getLocale()
        );
    }

    public function testCreateFromIsoStringWithTimeZone(): void
    {
        $this->assertSame(
            '1970-01-01 12:30:15.123 UTC',
            Time::createFromIsoString('12:30:15.123', 'Australia/Brisbane')
                ->toNativeDateTime()
                ->format('Y-m-d H:i:s.v e')
        );
    }

    public function testCreateFromNativeDateTime(): void
    {
        $dateTime = new DateTimeImmutable(
            '2018-12-31 14:30:15.123',
            new DateTimeZone('UTC')
        );

        $this->assertSame(
            '00:30:15.123',
            Time::createFromNativeDateTime(
                $dateTime,
                'Australia/Brisbane'
            )->toIsoString()
        );
    }

    public function testCreateFromTimestamp(): void
    {
        $this->assertSame(
            '12:30:15',
            Time::createFromTimestamp(1546345815)->toIsoString()
        );
    }

    public function testNow(): void
    {
        $this->assertSame(
            '1970-01-01',
            Time::now()->toNativeDateTime()->format('Y-m-d')
        );
    }

    public function testNowInstanceOf(): void
    {
        $this->assertInstanceOf(
            Time::class,
            Time::now()
        );
    }

    public function testNowWithLocale(): void
    {
        $this->assertSame(
            'ar-eg',
            Time::now(locale: 'ar-eg')->getLocale()
        );
    }

    public function testNowWithTimeZone(): void
    {
        $this->assertSame(
            'UTC',
            Time::now('Australia/Brisbane')->getTimeZone()
        );
    }
}
