<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\ServerRequest;
use PHPUnit\Framework\Attributes\DataProvider;

trait ProxyTestTrait
{
    /**
     * @return array<string, array{list<string>, array<string, array<string, string>>, string}>
     */
    public static function clientIpProxyChainProvider(): array
    {
        return [
            'malformed chain' => [
                ['10.0.0.1', '127.0.0.1'],
                [
                    'headers' => [
                        'X-Forwarded-For' => 'not-an-ip, 10.0.0.1',
                    ],
                    'server' => [
                        'REMOTE_ADDR' => '127.0.0.1',
                    ],
                ],
                '10.0.0.1',
            ],
            'first untrusted proxy' => [
                ['10.0.0.2', '10.0.0.3'],
                [
                    'headers' => [
                        'X-Forwarded-For' => '198.51.100.20, 203.0.113.10, 10.0.0.2',
                    ],
                    'server' => [
                        'REMOTE_ADDR' => '10.0.0.3',
                    ],
                ],
                '203.0.113.10',
            ],
            'trusted proxy' => [
                ['127.0.0.1'],
                [
                    'headers' => [
                        'X-Forwarded-For' => '203.0.113.10, 127.0.0.1',
                    ],
                    'server' => [
                        'REMOTE_ADDR' => '127.0.0.1',
                    ],
                ],
                '203.0.113.10',
            ],
            'untrusted proxy' => [
                ['10.0.0.1'],
                [
                    'headers' => [
                        'X-Forwarded-For' => '203.0.113.10',
                    ],
                    'server' => [
                        'REMOTE_ADDR' => '127.0.0.1',
                    ],
                ],
                '127.0.0.1',
            ],
        ];
    }

    public function testGetClientIp(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'X-Forwarded-For' => '203.0.113.10',
            ],
            'server' => [
                'REMOTE_ADDR' => '127.0.0.1',
            ],
        ]);

        $this->assertSame(
            '127.0.0.1',
            $request->getClientIp()
        );
    }

    /**
     * @param list<string> $trustedProxies
     * @param array<string, array<string, string>> $options
     */
    #[DataProvider('clientIpProxyChainProvider')]
    public function testGetClientIpProxyChain(array $trustedProxies, array $options, string $expected): void
    {
        $this->config
            ->set('App.trustProxy', true)
            ->set('App.trustedProxies', $trustedProxies);

        $request = new ServerRequest($this->config, $this->type, $options);

        $this->assertSame(
            $expected,
            $request->getClientIp()
        );
    }

    public function testGetClientIpTrustsLastForwardedIpWithoutTrustedProxies(): void
    {
        $this->config->set('App.trustProxy', true);

        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'X-Forwarded-For' => '198.51.100.20, 203.0.113.10',
            ],
            'server' => [
                'REMOTE_ADDR' => '127.0.0.1',
            ],
        ]);

        $this->assertSame(
            '203.0.113.10',
            $request->getClientIp()
        );
    }

    public function testGetTrustedProxies(): void
    {
        $request1 = new ServerRequest($this->config, $this->type);

        $this->config->set('App.trustedProxies', ['127.0.0.1']);

        $request2 = new ServerRequest($this->config, $this->type);

        $this->assertArraysAreIdentical(
            [],
            $request1->getTrustedProxies()
        );

        $this->assertArraysAreIdentical(
            ['127.0.0.1'],
            $request2->getTrustedProxies()
        );
    }

    public function testIsSecureForwardedProto(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'X-Forwarded-Proto' => 'https',
            ],
            'server' => [
                'REMOTE_ADDR' => '203.0.113.10',
            ],
        ]);

        $this->assertFalse(
            $request->isSecure()
        );
    }

    public function testIsSecureForwardedProtoFromTrustedProxy(): void
    {
        $this->config
            ->set('App.trustProxy', true)
            ->set('App.trustedProxies', ['127.0.0.1']);

        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'X-Forwarded-Proto' => 'https',
            ],
            'server' => [
                'REMOTE_ADDR' => '127.0.0.1',
            ],
        ]);

        $this->assertTrue(
            $request->isSecure()
        );
    }

    public function testIsSecureForwardedProtoWithoutTrustedProxies(): void
    {
        $this->config->set('App.trustProxy', true);

        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'X-Forwarded-Proto' => 'https',
            ],
            'server' => [
                'REMOTE_ADDR' => '127.0.0.1',
            ],
        ]);

        $this->assertTrue(
            $request->isSecure()
        );
    }
}
