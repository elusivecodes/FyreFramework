<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\ServerRequest;
use InvalidArgumentException;

trait NegotiateTestTrait
{
    public function testNegotiateEncoding(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'Accept-Encoding' => 'gzip,deflate',
            ],
        ]);

        $this->assertSame(
            'gzip',
            $request->negotiate('encoding', ['deflate', 'gzip'])
        );
    }

    public function testNegotiateInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Negotiation type `invalid` is not valid.');

        $request = new ServerRequest($this->config, $this->type);

        // @phpstan-ignore argument.type
        $request->negotiate('invalid', []);
    }

    public function testNegotiateLanguage(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'Accept-Language' => 'en-gb,en;q=0.5',
            ],
        ]);

        $this->assertSame(
            'en-gb',
            $request->negotiate('language', ['en-gb'])
        );
    }

    public function testNegotiateMedia(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,/;q=0.8',
            ],
        ]);

        $this->assertSame(
            'text/html',
            $request->negotiate('content', ['application/xml', 'text/html'])
        );
    }

    public function testPrefersJson(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'Accept' => 'application/json,text/html;q=0.9',
            ],
        ]);

        $this->assertTrue(
            $request->prefersJson()
        );
    }

    public function testPrefersJsonFalse(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'Accept' => 'text/html,application/json;q=0.9',
            ],
        ]);

        $this->assertFalse(
            $request->prefersJson()
        );
    }
}
