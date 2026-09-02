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

    public function testCreateFromArrayWithLocaleAndTimeZone(): void
    {
        $time = Time::createFromArray(
            [12, 30, 15, 123],
            'Australia/Brisbane',
            'ar-eg'
        );

        $this->assertArraysAreIdentical(
            ['12:30:15.123', 'UTC', 'ar-eg'],
            [$time->toIsoString(), $time->getTimeZone(), $time->getLocale()]
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

    public function testCreateFromIsoStringWithLocaleAndTimeZone(): void
    {
        $time = Time::createFromIsoString(
            '12:30:15.123',
            'Australia/Brisbane',
            'ar-eg'
        );

        $this->assertArraysAreIdentical(
            ['12:30:15.123', 'UTC', 'ar-eg'],
            [$time->toIsoString(), $time->getTimeZone(), $time->getLocale()]
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
        $time = Time::now('Australia/Brisbane', 'ar-eg');

        $this->assertInstanceOf(
            Time::class,
            $time
        );
        $this->assertArraysAreIdentical(
            ['UTC', 'ar-eg', '1970-01-01'],
            [
                $time->getTimeZone(),
                $time->getLocale(),
                $time->toNativeDateTime()->format('Y-m-d'),
            ]
        );
    }
}
