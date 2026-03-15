<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\ServerRequest;

trait ProxyTestTrait
{
    public function testGetClientIp(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'REMOTE_ADDR' => '127.0.0.1',
                'HTTP_X_FORWARDED_FOR' => '203.0.113.10',
            ],
        ]);

        $this->assertSame(
            '127.0.0.1',
            $request->getClientIp()
        );
    }

    public function testGetClientIpTrustedProxy(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'REMOTE_ADDR' => '127.0.0.1',
                'HTTP_X_FORWARDED_FOR' => '203.0.113.10, 127.0.0.1',
            ],
        ]);

        $request = $request
            ->trustProxy()
            ->setTrustedProxies(['127.0.0.1']);

        $this->assertSame(
            '203.0.113.10',
            $request->getClientIp()
        );
    }

    public function testGetClientIpUntrustedProxy(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'REMOTE_ADDR' => '127.0.0.1',
                'HTTP_X_FORWARDED_FOR' => '203.0.113.10',
            ],
        ]);

        $request = $request
            ->trustProxy()
            ->setTrustedProxies(['10.0.0.1']);

        $this->assertSame(
            '127.0.0.1',
            $request->getClientIp()
        );
    }

    public function testGetTrustedProxies(): void
    {
        $request1 = new ServerRequest($this->config, $this->type);
        $request2 = $request1->setTrustedProxies(['127.0.0.1']);

        $this->assertSame(
            [],
            $request1->getTrustedProxies()
        );

        $this->assertSame(
            ['127.0.0.1'],
            $request2->getTrustedProxies()
        );
    }

    public function testIsSecureForwardedProto(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'HTTP_X_FORWARDED_PROTO' => 'https',
            ],
        ]);

        $this->assertTrue(
            $request->isSecure()
        );
    }

    public function testTrustProxy(): void
    {
        $request1 = new ServerRequest($this->config, $this->type, [
            'server' => [
                'REMOTE_ADDR' => '127.0.0.1',
                'HTTP_X_FORWARDED_FOR' => '203.0.113.10',
            ],
        ]);
        $request2 = $request1->trustProxy();

        $this->assertSame(
            '127.0.0.1',
            $request1->getClientIp()
        );

        $this->assertSame(
            '203.0.113.10',
            $request2->getClientIp()
        );
    }
}
