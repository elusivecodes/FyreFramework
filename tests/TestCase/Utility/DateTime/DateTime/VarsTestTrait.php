<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;

trait VarsTestTrait
{
    public function testFormatsAtom(): void
    {
        $this->assertSame(
            '2020-01-01T00:00:00+00:00',
            DateTime::createFromArray([2020])->format(DateTime::FORMATS['atom'])
        );
    }

    public function testFormatsCookie(): void
    {
        $this->assertSame(
            'Wednesday, 01-Jan-2020 00:00:00 GMT',
            DateTime::createFromArray([2020])->format(DateTime::FORMATS['cookie'])
        );
    }

    public function testFormatsDate(): void
    {
        $this->assertSame(
            'Wed Jan 01 2020',
            DateTime::createFromArray([2020])->format(DateTime::FORMATS['date'])
        );
    }

    public function testFormatsIso8601(): void
    {
        $this->assertSame(
            '2020-01-01T00:00:00+0000',
            DateTime::createFromArray([2020])->format(DateTime::FORMATS['iso8601'])
        );
    }

    public function testFormatsRfc1036(): void
    {
        $this->assertSame(
            'Wed, 01 Jan 20 00:00:00 +0000',
            DateTime::createFromArray([2020])->format(DateTime::FORMATS['rfc1036'])
        );
    }

    public function testFormatsRfc1123(): void
    {
        $this->assertSame(
            'Wed, 01 Jan 2020 00:00:00 +0000',
            DateTime::createFromArray([2020])->format(DateTime::FORMATS['rfc1123'])
        );
    }

    public function testFormatsRfc2822(): void
    {
        $this->assertSame(
            'Wed, 01 Jan 2020 00:00:00 +0000',
            DateTime::createFromArray([2020])->format(DateTime::FORMATS['rfc2822'])
        );
    }

    public function testFormatsRfc3339(): void
    {
        $this->assertSame(
            '2020-01-01T00:00:00+00:00',
            DateTime::createFromArray([2020])->format(DateTime::FORMATS['rfc3339'])
        );
    }

    public function testFormatsRfc3339Extended(): void
    {
        $this->assertSame(
            '2020-01-01T00:00:00.000+00:00',
            DateTime::createFromArray([2020])->format(DateTime::FORMATS['rfc3339_extended'])
        );
    }

    public function testFormatsRfc822(): void
    {
        $this->assertSame(
            'Wed, 01 Jan 20 00:00:00 +0000',
            DateTime::createFromArray([2020])->format(DateTime::FORMATS['rfc822'])
        );
    }

    public function testFormatsRfc850(): void
    {
        $this->assertSame(
            'Wednesday 01-Jan-20 00:00:00 GMT',
            DateTime::createFromArray([2020])->format(DateTime::FORMATS['rfc850'])
        );
    }

    public function testFormatsRss(): void
    {
        $this->assertSame(
            'Wed, 01 Jan 2020 00:00:00 +0000',
            DateTime::createFromArray([2020])->format(DateTime::FORMATS['rss'])
        );
    }

    public function testFormatsString(): void
    {
        $this->assertSame(
            'Wed Jan 01 2020 00:00:00 +0000 (UTC)',
            DateTime::createFromArray([2020])->format(DateTime::FORMATS['string'])
        );
    }

    public function testFormatsTime(): void
    {
        $this->assertSame(
            '00:00:00 +0000 (UTC)',
            DateTime::createFromArray([2020])->format(DateTime::FORMATS['time'])
        );
    }

    public function testFormatsW3c(): void
    {
        $this->assertSame(
            '2020-01-01T00:00:00+00:00',
            DateTime::createFromArray([2020])->format(DateTime::FORMATS['w3c'])
        );
    }
}
