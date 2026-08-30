<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\ServerRequest;
use Fyre\Utility\DateTime\DateTime;

trait QueryTestTrait
{
    public function testGetQuery(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'get' => [
                'test' => 'value',
            ],
        ]);

        $this->assertSame(
            'value',
            $request->getQuery('test')
        );
    }

    public function testGetQueryAll(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'get' => [
                'test' => 'value',
            ],
        ]);

        $this->assertArraysAreIdentical(
            [
                'test' => 'value',
            ],
            $request->getQuery()
        );
    }

    public function testGetQueryArray(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'get' => [
                'test' => [
                    'a' => 'value',
                ],
            ],
        ]);

        $this->assertArraysAreIdentical(
            [
                'a' => 'value',
            ],
            $request->getQuery('test')
        );
    }

    public function testGetQueryDot(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'get' => [
                'test' => [
                    'a' => 'value',
                ],
            ],
        ]);

        $this->assertSame(
            'value',
            $request->getQuery('test.a')
        );
    }

    public function testGetQueryFilter(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'get' => [
                'test' => '2024-12-31',
            ],
        ]);

        $value = $request->getQuery('test', 'date');

        $this->assertInstanceOf(
            DateTime::class,
            $value
        );

        $this->assertSame(
            '2024-12-31T00:00:00.000+00:00',
            $value->toIsoString()
        );
    }

    public function testGetQueryInvalid(): void
    {
        $request = new ServerRequest($this->config, $this->type);

        $this->assertNull(
            $request->getQuery('invalid')
        );
    }

    public function testWithQueryParams(): void
    {
        $request1 = new ServerRequest($this->config, $this->type);
        $request2 = $request1->withQueryParams(['test' => 'value']);

        $this->assertArraysAreIdentical(
            [],
            $request1->getQueryParams()
        );

        $this->assertArraysAreIdentical(
            [
                'test' => 'value',
            ],
            $request2->getQueryParams()
        );
    }
}
