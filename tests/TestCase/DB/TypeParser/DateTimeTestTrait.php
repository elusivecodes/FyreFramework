<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

use Fyre\Utility\DateTime\DateTime;

trait DateTimeTestTrait
{
    public function testDateTimeFromDatabase(): void
    {
        $this->assertSame(
            '2021-12-31T22:59:11.000+00:00',
            $this->type->use('datetime')->fromDatabase('2021-12-31 22:59:11')->toISOString()
        );
    }

    public function testDateTimeFromDatabaseFractional(): void
    {
        $this->assertSame(
            '2021-12-31T22:59:11.123+00:00',
            $this->type->use('datetime')->fromDatabase('2021-12-31 22:59:11.12345')->toISOString()
        );
    }

    public function testDateTimeFromDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('datetime')->fromDatabase(null)
        );
    }

    public function testDateTimeFromDatabaseServerTimeZone(): void
    {
        $dateParser = $this->type->use('datetime');

        $dateParser->setServerTimeZone('Australia/Brisbane');

        $this->assertSame(
            '2021-12-31T12:59:11.000+00:00',
            $dateParser->fromDatabase('2021-12-31 22:59:11')->toISOString()
        );
    }

    public function testDateTimeFromDatabaseTimestamp(): void
    {
        $this->assertSame(
            '2021-12-31T22:59:11.000+00:00',
            $this->type->use('datetime')->fromDatabase(1640991551)->toISOString()
        );
    }

    public function testDateTimeFromDatabaseUserTimeZone(): void
    {
        $dateParser = $this->type->use('datetime');

        $dateParser->setUserTimeZone('Australia/Brisbane');

        $this->assertSame(
            'Australia/Brisbane',
            $dateParser->fromDatabase('2021-12-31 22:59:11')->getTimeZone()
        );
    }

    public function testDateTimeParse(): void
    {
        $date = $this->type->use('datetime')->parse('2022-01-01T08:59:11');

        $this->assertInstanceOf(
            DateTime::class,
            $date
        );

        $this->assertSame(
            '2022-01-01T08:59:11.000+00:00',
            $date->toISOString()
        );
    }

    public function testDateTimeParseDateTime(): void
    {
        $date = DateTime::createFromTimestamp(1640991551);

        $this->assertSame(
            $date,
            $this->type->use('datetime')->parse($date)
        );
    }

    public function testDateTimeParseInvalid(): void
    {
        $this->assertNull(
            $this->type->use('datetime')->parse('invalid')
        );
    }

    public function testDateTimeParseLocaleFormat(): void
    {
        $dateParser = $this->type->use('datetime');

        $this->assertSame(
            $dateParser,
            $dateParser->setLocaleFormat('eee MMM dd yyyy HH:mm:ss')
        );

        $this->assertSame(
            '2022-01-01T11:59:00.000+00:00',
            $dateParser->parse('Sat Jan 01 2022 11:59:00')->toISOString()
        );
    }

    public function testDateTimeParseLocaleFormatFallback(): void
    {
        $dateParser = $this->type->use('datetime');

        $this->assertSame(
            $dateParser,
            $dateParser->setLocaleFormat('eee MMM dd yyyy HH:mm:ss')
        );

        $this->assertSame(
            '2022-01-01T11:59:00.000+00:00',
            $dateParser->parse('2022-01-01T11:59:00')->toISOString()
        );
    }

    public function testDateTimeParseNative(): void
    {
        $date = new \DateTime('@1640991551');

        $this->assertSame(
            '2021-12-31T22:59:11.000+00:00',
            $this->type->use('datetime')->parse($date)->toISOString()
        );
    }

    public function testDateTimeParseNull(): void
    {
        $this->assertNull(
            $this->type->use('datetime')->parse(null)
        );
    }

    public function testDateTimeParseTimestamp(): void
    {
        $this->assertSame(
            '2021-12-31T22:59:11.000+00:00',
            $this->type->use('datetime')->parse(1640991551)->toISOString()
        );
    }

    public function testDateTimeParseUserTimeZone(): void
    {
        $dateParser = $this->type->use('datetime');

        $dateParser->setUserTimeZone('Australia/Brisbane');
        $dateParser->setLocaleFormat('eee MMM dd yyyy HH:mm:ss');

        $this->assertSame(
            '2021-12-31T14:00:00.000+00:00',
            $dateParser->parse('Sat Jan 01 2022 00:00:00')->toISOString()
        );
    }

    public function testDateTimeSetLocaleFormat(): void
    {
        $dateParser = $this->type->use('datetime');

        $this->assertSame(
            $dateParser,
            $dateParser->setLocaleFormat('eee MMM dd yyyy HH:mm:ss')
        );

        $this->assertSame(
            'eee MMM dd yyyy HH:mm:ss',
            $dateParser->getLocaleFormat()
        );
    }

    public function testDateTimeSetServerTimeZone(): void
    {
        $dateParser = $this->type->use('datetime');

        $this->assertSame(
            $dateParser,
            $dateParser->setServerTimeZone('Australia/Brisbane')
        );

        $this->assertSame(
            'Australia/Brisbane',
            $dateParser->getServerTimeZone()
        );
    }

    public function testDateTimeSetUserTimeZone(): void
    {
        $dateParser = $this->type->use('datetime');

        $this->assertSame(
            $dateParser,
            $dateParser->setUserTimeZone('Australia/Brisbane')
        );

        $this->assertSame(
            'Australia/Brisbane',
            $dateParser->getUserTimeZone()
        );
    }

    public function testDateTimeToDatabase(): void
    {
        $date = DateTime::createFromTimestamp(1640991551);

        $this->assertSame(
            '2021-12-31 22:59:11',
            $this->type->use('datetime')->toDatabase($date)
        );
    }

    public function testDateTimeToDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('datetime')->toDatabase(null)
        );
    }

    public function testDateTimeToDatabaseServerTimeZone(): void
    {
        $dateParser = $this->type->use('datetime');

        $dateParser->setServerTimeZone('Australia/Brisbane');

        $date = DateTime::createFromTimestamp(1640991551);

        $this->assertSame(
            '2022-01-01 08:59:11',
            $dateParser->toDatabase($date)
        );
    }

    public function testDateTimeToDatabaseString(): void
    {
        $this->assertSame(
            '2021-12-31 22:59:11',
            $this->type->use('datetime')->toDatabase('2021-12-31 22:59:11')
        );
    }
}
