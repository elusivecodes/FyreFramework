<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\ServerRequest;
use Fyre\Utility\DateTime\DateTime;

trait ServerTestTrait
{
    public function testGetServer(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'test' => 'value',
            ],
        ]);

        $this->assertSame(
            'value',
            $request->getServer('test')
        );
    }

    public function testGetServerAll(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'test' => 'value',
            ],
        ]);

        $this->assertSame(
            [
                'test' => 'value',
            ],
            $request->getServer()
        );
    }

    public function testGetServerFilter(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'test' => '2024-12-31',
            ],
        ]);

        $value = $request->getServer('test', 'date');

        $this->assertInstanceOf(
            DateTime::class,
            $value
        );

        $this->assertSame(
            '2024-12-31T00:00:00.000+00:00',
            $value->toISOString()
        );
    }

    public function testGetServerInvalid(): void
    {
        $request = new ServerRequest($this->config, $this->type);

        $this->assertNull(
            $request->getServer('invalid')
        );
    }

    public function testServerContentType(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'CONTENT_TYPE' => 'application/json',
            ],
        ]);

        $this->assertSame(
            'application/json',
            $request->getHeaderLine('Content-Type')
        );
    }

    public function testServerMethod(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'REQUEST_METHOD' => 'POST',
            ],
        ]);

        $this->assertSame(
            'POST',
            $request->getMethod()
        );
    }

    public function testWithServerParams(): void
    {
        $request1 = new ServerRequest($this->config, $this->type, [
            'server' => [],
        ]);
        $request2 = $request1->withServerParams(['test' => 'value']);

        $this->assertEmpty(
            $request1->getServerParams()
        );

        $this->assertSame(
            [
                'test' => 'value',
            ],
            $request2->getServerParams()
        );
    }
}
