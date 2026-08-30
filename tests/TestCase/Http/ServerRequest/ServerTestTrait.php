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

        $this->assertArraysAreIdentical(
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
            $value->toIsoString()
        );
    }

    public function testGetServerInvalid(): void
    {
        $request = new ServerRequest($this->config, $this->type);

        $this->assertNull(
            $request->getServer('invalid')
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

        $this->assertArraysAreIdentical(
            [
                'test' => 'value',
            ],
            $request2->getServerParams()
        );
    }
}
