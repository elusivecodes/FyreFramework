<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Date;

use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeZone;
use Fyre\Utility\DateTime\Date;
use PHPUnit\Framework\Attributes\DataProvider;

trait CreateTestTrait
{
    /**
     * @return array<string, array{string}>
     */
    public static function invalidIsoStringProvider(): array
    {
        return [
            'date time' => ['2019-01-01T00:00:00.000+00:00'],
            'invalid' => ['invalid'],
            'non-padded' => ['2019-1-1'],
            'overflow' => ['2019-02-29'],
        ];
    }

    public function testConstructor(): void
    {
        $date = new Date('January 1, 2019 23:30:15.123');

        $this->assertArraysAreIdentical(
            [2019, 1, 1],
            [$date->getYear(), $date->getMonth(), $date->getDate()]
        );
        $this->assertSame(
            '2019-01-01 00:00:00.000 UTC',
            $date->toNativeDateTime()->format('Y-m-d H:i:s.v e')
        );
    }

    public function testConstructorWithTimeZone(): void
    {
        $date = new Date(
            'January 1, 2019 23:30:15.123',
            'Australia/Brisbane'
        );

        $this->assertSame(
            '2019-01-01 00:00:00.000 UTC',
            $date->toNativeDateTime()->format('Y-m-d H:i:s.v e')
        );
    }

    public function testCreateFromArray(): void
    {
        $this->assertSame(
            '2019-01-01',
            Date::createFromArray([2019])->toIsoString()
        );
    }

    public function testCreateFromArrayWithLocale(): void
    {
        $this->assertSame(
            'ar-eg',
            Date::createFromArray([2019, 1, 1], locale: 'ar-eg')->getLocale()
        );
    }

    public function testCreateFromArrayWithTimeZone(): void
    {
        $this->assertSame(
            '2019-01-01 00:00:00.000 UTC',
            Date::createFromArray([2019, 1, 1], 'Australia/Brisbane')
                ->toNativeDateTime()
                ->format('Y-m-d H:i:s.v e')
        );
    }

    public function testCreateFromIsoString(): void
    {
        $this->assertSame(
            '2019-01-01',
            Date::createFromIsoString('2019-01-01')->toIsoString()
        );
    }

    #[DataProvider('invalidIsoStringProvider')]
    public function testCreateFromIsoStringInvalid(string $value): void
    {
        $this->expectException(DateMalformedStringException::class);
        $this->expectExceptionMessageIs('Date string is not valid ISO 8601.');

        Date::createFromIsoString($value);
    }

    public function testCreateFromIsoStringRoundTrip(): void
    {
        $date = Date::createFromArray([2019, 1, 1]);

        $this->assertSame(
            $date->toIsoString(),
            Date::createFromIsoString($date->toIsoString())->toIsoString()
        );
    }

    public function testCreateFromIsoStringWithLocale(): void
    {
        $this->assertSame(
            'ar-eg',
            Date::createFromIsoString('2019-01-01', locale: 'ar-eg')->getLocale()
        );
    }

    public function testCreateFromIsoStringWithTimeZone(): void
    {
        $this->assertSame(
            '2019-01-01 00:00:00.000 UTC',
            Date::createFromIsoString('2019-01-01', 'Australia/Brisbane')
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
            '2019-01-01',
            Date::createFromNativeDateTime(
                $dateTime,
                'Australia/Brisbane'
            )->toIsoString()
        );
    }

    public function testCreateFromTimestamp(): void
    {
        $this->assertSame(
            '2019-01-01',
            Date::createFromTimestamp(1546345815)->toIsoString()
        );
    }

    public function testNow(): void
    {
        $this->assertSame(
            '00:00:00.000',
            Date::now()->toNativeDateTime()->format('H:i:s.v')
        );
    }

    public function testNowInstanceOf(): void
    {
        $this->assertInstanceOf(
            Date::class,
            Date::now()
        );
    }

    public function testNowWithLocale(): void
    {
        $this->assertSame(
            'ar-eg',
            Date::now(locale: 'ar-eg')->getLocale()
        );
    }

    public function testNowWithTimeZone(): void
    {
        $this->assertSame(
            'UTC',
            Date::now('Australia/Brisbane')->getTimeZone()
        );
    }
}
