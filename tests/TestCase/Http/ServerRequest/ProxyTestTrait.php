<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\ServerRequest;

trait ProxyTestTrait
{
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

    public function testGetClientIpReturnsLastValidIpForMalformedChain(): void
    {
        $this->config
            ->set('App.trustProxy', true)
            ->set('App.trustedProxies', ['10.0.0.1', '127.0.0.1']);

        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'X-Forwarded-For' => 'not-an-ip, 10.0.0.1',
            ],
            'server' => [
                'REMOTE_ADDR' => '127.0.0.1',
            ],
        ]);

        $this->assertSame(
            '10.0.0.1',
            $request->getClientIp()
        );
    }

    public function testGetClientIpStopsAtFirstUntrustedProxy(): void
    {
        $this->config
            ->set('App.trustProxy', true)
            ->set('App.trustedProxies', ['10.0.0.2', '10.0.0.3']);

        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'X-Forwarded-For' => '198.51.100.20, 203.0.113.10, 10.0.0.2',
            ],
            'server' => [
                'REMOTE_ADDR' => '10.0.0.3',
            ],
        ]);

        $this->assertSame(
            '203.0.113.10',
            $request->getClientIp()
        );
    }

    public function testGetClientIpTrustedProxy(): void
    {
        $this->config
            ->set('App.trustProxy', true)
            ->set('App.trustedProxies', ['127.0.0.1']);

        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'X-Forwarded-For' => '203.0.113.10, 127.0.0.1',
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

    public function testGetClientIpUntrustedProxy(): void
    {
        $this->config
            ->set('App.trustProxy', true)
            ->set('App.trustedProxies', ['10.0.0.1']);

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
