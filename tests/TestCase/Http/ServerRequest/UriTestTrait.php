<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\ServerRequest;
use Fyre\Http\Uri;

trait UriTestTrait
{
    public function testServerUri(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'HTTPS' => 'on',
                'HTTP_HOST' => 'test.com',
            ],
        ]);

        $this->assertSame(
            'https://test.com',
            $request->getUri()->getUri()
        );
    }

    public function testServerUriPath(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'REQUEST_URI' => '/test/path',
            ],
        ]);

        $this->assertSame(
            '/test/path',
            $request->getUri()->getPath()
        );
    }

    public function testServerUriQuery(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'QUERY_STRING' => '?a=1&b=2',
            ],
        ]);

        $this->assertSame(
            'a=1&b=2',
            $request->getUri()->getQuery()
        );
    }

    public function testUri(): void
    {
        $request = new ServerRequest($this->config, $this->type);

        $this->assertInstanceOf(
            Uri::class,
            $request->getUri()
        );
    }
}
