<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

use Fyre\Utility\DateTime\DateTime;

trait DateTestTrait
{
    public function testDateFromDatabase(): void
    {
        $this->assertSame(
            '2021-12-31T00:00:00.000+00:00',
            $this->type->use('date')->fromDatabase('2021-12-31')->toISOString()
        );
    }

    public function testDateFromDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('date')->fromDatabase(null)
        );
    }

    public function testDateFromDatabaseServerTimeZone(): void
    {
        $dateParser = $this->type->use('date');

        $dateParser->setServerTimeZone('Australia/Brisbane');

        $this->assertSame(
            '2021-12-30T14:00:00.000+00:00',
            $dateParser->fromDatabase('2021-12-31')->toISOString()
        );
    }

    public function testDateFromDatabaseTimestamp(): void
    {
        $this->assertSame(
            '2021-12-31T00:00:00.000+00:00',
            $this->type->use('date')->fromDatabase(1640991551)->toISOString()
        );
    }

    public function testDateFromDatabaseUserTimeZone(): void
    {
        $dateParser = $this->type->use('date');

        $dateParser->setUserTimeZone('Australia/Brisbane');

        $this->assertSame(
            'Australia/Brisbane',
            $dateParser->fromDatabase('2021-12-31')->getTimeZone()
        );
    }

    public function testDateParse(): void
    {
        $date = $this->type->use('date')->parse('2022-01-01');

        $this->assertInstanceOf(
            DateTime::class,
            $date
        );

        $this->assertSame(
            '2022-01-01T00:00:00.000+00:00',
            $date->toISOString()
        );
    }

    public function testDateParseDateTime(): void
    {
        $date = DateTime::createFromTimestamp(1640991551);

        $this->assertSame(
            '2021-12-31T00:00:00.000+00:00',
            $this->type->use('date')->parse($date)->toISOString()
        );
    }

    public function testDateParseInvalid(): void
    {
        $this->assertNull(
            $this->type->use('date')->parse('invalid')
        );
    }

    public function testDateParseLocaleFormat(): void
    {
        $dateParser = $this->type->use('date');

        $this->assertSame(
            $dateParser,
            $dateParser->setLocaleFormat('eee MMM dd yyyy')
        );

        $this->assertSame(
            '2022-01-01T00:00:00.000+00:00',
            $dateParser->parse('Sat Jan 01 2022')->toISOString()
        );
    }

    public function testDateParseLocaleFormatFallback(): void
    {
        $dateParser = $this->type->use('date');

        $this->assertSame(
            $dateParser,
            $dateParser->setLocaleFormat('eee MMM dd yyyy')
        );

        $this->assertSame(
            '2022-01-01T00:00:00.000+00:00',
            $dateParser->parse('2022-01-01')->toISOString()
        );
    }

    public function testDateParseNative(): void
    {
        $date = new \DateTime('@1640991551');

        $this->assertSame(
            '2021-12-31T00:00:00.000+00:00',
            $this->type->use('date')->parse($date)->toISOString()
        );
    }

    public function testDateParseNull(): void
    {
        $this->assertNull(
            $this->type->use('date')->parse(null)
        );
    }

    public function testDateParseTimestamp(): void
    {
        $this->assertSame(
            '2021-12-31T00:00:00.000+00:00',
            $this->type->use('date')->parse(1640991551)->toISOString()
        );
    }

    public function testDateParseUserTimeZone(): void
    {
        $dateParser = $this->type->use('date');

        $dateParser->setUserTimeZone('Australia/Brisbane');
        $dateParser->setLocaleFormat('eee MMM dd yyyy');

        $this->assertSame(
            '2021-12-31T14:00:00.000+00:00',
            $dateParser->parse('Sat Jan 01 2022')->toISOString()
        );
    }

    public function testDateSetLocaleFormat(): void
    {
        $dateParser = $this->type->use('date');

        $this->assertSame(
            $dateParser,
            $dateParser->setLocaleFormat('eee MMM dd yyyy')
        );

        $this->assertSame(
            'eee MMM dd yyyy',
            $dateParser->getLocaleFormat()
        );
    }

    public function testDateSetServerTimeZone(): void
    {
        $dateParser = $this->type->use('date');

        $this->assertSame(
            $dateParser,
            $dateParser->setServerTimeZone('Australia/Brisbane')
        );

        $this->assertSame(
            'Australia/Brisbane',
            $dateParser->getServerTimeZone()
        );
    }

    public function testDateSetUserTimeZone(): void
    {
        $dateParser = $this->type->use('date');

        $this->assertSame(
            $dateParser,
            $dateParser->setUserTimeZone('Australia/Brisbane')
        );

        $this->assertSame(
            'Australia/Brisbane',
            $dateParser->getUserTimeZone()
        );
    }

    public function testDateToDatabase(): void
    {
        $date = DateTime::createFromTimestamp(1640991551);

        $this->assertSame(
            '2021-12-31',
            $this->type->use('date')->toDatabase($date)
        );
    }

    public function testDateToDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('date')->toDatabase(null)
        );
    }

    public function testDateToDatabaseServerTimeZone(): void
    {
        $dateParser = $this->type->use('date');

        $dateParser->setServerTimeZone('Australia/Brisbane');

        $date = DateTime::createFromTimestamp(1640991551);

        $this->assertSame(
            '2021-12-31',
            $dateParser->toDatabase($date)
        );
    }

    public function testDateToDatabaseString(): void
    {
        $this->assertSame(
            '2021-12-31',
            $this->type->use('date')->toDatabase('2021-12-31')
        );
    }
}
