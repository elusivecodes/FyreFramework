<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\ServerRequest;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

trait NegotiateTestTrait
{
    /**
     * @return array<string, array{'content'|'encoding'|'language', array<string, string>, string[], string}>
     */
    public static function negotiateProvider(): array
    {
        return [
            'encoding' => [
                'encoding',
                [
                    'Accept-Encoding' => 'gzip,deflate',
                ],
                ['deflate', 'gzip'],
                'gzip',
            ],
            'language' => [
                'language',
                [
                    'Accept-Language' => 'en-gb,en;q=0.5',
                ],
                ['en-gb'],
                'en-gb',
            ],
            'media' => [
                'content',
                [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,/;q=0.8',
                ],
                ['application/xml', 'text/html'],
                'text/html',
            ],
        ];
    }

    /**
     * @param 'content'|'encoding'|'language' $type
     * @param array<string, string> $headers
     * @param string[] $supported
     */
    #[DataProvider('negotiateProvider')]
    public function testNegotiate(string $type, array $headers, array $supported, string $expected): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'headers' => $headers,
        ]);

        $this->assertSame(
            $expected,
            $request->negotiate($type, $supported)
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
