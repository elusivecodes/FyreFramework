<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ClientResponse;

use Fyre\Http\ClientResponse;
use Fyre\Utility\DateTime\DateTime;

trait DateTestTrait
{
    public function testWithDate(): void
    {
        $response1 = new ClientResponse();
        $response2 = $response1->withDate('0');

        $this->assertSame(
            '',
            $response1->getHeaderLine('Date')
        );

        $this->assertSame(
            'Thu, 01-Jan-1970 00:00:00 UTC',
            $response2->getHeaderLine('Date')
        );
    }

    public function testWithDateDateTime(): void
    {
        $date = DateTime::createFromTimestamp(0);

        $response1 = new ClientResponse();
        $response2 = $response1->withDate($date);

        $this->assertSame(
            '',
            $response1->getHeaderLine('Date')
        );

        $this->assertSame(
            'Thu, 01-Jan-1970 00:00:00 UTC',
            $response2->getHeaderLine('Date')
        );
    }

    public function testWithDateNativeDateTime(): void
    {
        $date = new \DateTime('@0');

        $response1 = new ClientResponse();
        $response2 = $response1->withDate($date);

        $this->assertSame(
            '',
            $response1->getHeaderLine('Date')
        );

        $this->assertSame(
            'Thu, 01-Jan-1970 00:00:00 UTC',
            $response2->getHeaderLine('Date')
        );
    }

    public function testWithLastModified(): void
    {
        $response1 = new ClientResponse();
        $response2 = $response1->withLastModified('0');

        $this->assertSame(
            '',
            $response1->getHeaderLine('Last-Modified')
        );

        $this->assertSame(
            'Thu, 01-Jan-1970 00:00:00 UTC',
            $response2->getHeaderLine('Last-Modified')
        );
    }

    public function testWithLastModifiedDateTime(): void
    {
        $date = DateTime::createFromTimestamp(0);

        $response1 = new ClientResponse();
        $response2 = $response1->withLastModified($date);

        $this->assertSame(
            '',
            $response1->getHeaderLine('Last-Modified')
        );

        $this->assertSame(
            'Thu, 01-Jan-1970 00:00:00 UTC',
            $response2->getHeaderLine('Last-Modified')
        );
    }

    public function testWithLastModifiedNativeDateTime(): void
    {
        $date = new \DateTime('@0');

        $response1 = new ClientResponse();
        $response2 = $response1->withLastModified($date);

        $this->assertSame(
            '',
            $response1->getHeaderLine('Last-Modified')
        );

        $this->assertSame(
            'Thu, 01-Jan-1970 00:00:00 UTC',
            $response2->getHeaderLine('Last-Modified')
        );
    }
}
