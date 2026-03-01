<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\ServerRequest;
use Fyre\Utility\DateTime\DateTime;

use function json_encode;

trait DataTestTrait
{
    public function testGetData(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'data' => [
                'test' => 'value',
            ],
        ]);

        $this->assertSame(
            'value',
            $request->getData('test')
        );
    }

    public function testGetDataAll(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'data' => [
                'test' => 'value',
            ],
        ]);

        $this->assertSame(
            [
                'test' => 'value',
            ],
            $request->getData()
        );
    }

    public function testGetDataArray(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'data' => [
                'test' => [
                    'a' => 'value',
                ],
            ],
        ]);

        $this->assertSame(
            [
                'a' => 'value',
            ],
            $request->getData('test')
        );
    }

    public function testGetDataDot(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'data' => [
                'test' => [
                    'a' => 'value',
                ],
            ],
        ]);

        $this->assertSame(
            'value',
            $request->getData('test.a')
        );
    }

    public function testGetDataInvalid(): void
    {
        $request = new ServerRequest($this->config, $this->type);

        $this->assertNull(
            $request->getData('invalid')
        );
    }

    public function testGetDataJson(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'CONTENT_TYPE' => 'application/json',
            ],
            'body' => json_encode([
                'test' => 'value',
            ]),
        ]);

        $this->assertSame(
            'value',
            $request->getData('test')
        );
    }

    public function testGetDataJsonAll(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'CONTENT_TYPE' => 'application/json',
            ],
            'body' => json_encode([
                'test' => 'value',
            ]),
        ]);

        $this->assertSame(
            [
                'test' => 'value',
            ],
            $request->getData()
        );
    }

    public function testGetDataJsonArray(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'CONTENT_TYPE' => 'application/json',
            ],
            'body' => json_encode([
                'test' => [
                    'a' => 'value',
                ],
            ]),
        ]);

        $this->assertSame(
            [
                'a' => 'value',
            ],
            $request->getData('test')
        );
    }

    public function testGetDataJsonDot(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'server' => [
                'CONTENT_TYPE' => 'application/json',
            ],
            'body' => json_encode([
                'test' => [
                    'a' => 'value',
                ],
            ]),
        ]);

        $this->assertSame(
            'value',
            $request->getData('test.a')
        );
    }

    public function testGetDataType(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'data' => [
                'test' => '2024-12-31',
            ],
        ]);

        $value = $request->getData('test', 'date');

        $this->assertInstanceOf(
            DateTime::class,
            $value
        );

        $this->assertSame(
            '2024-12-31T00:00:00.000+00:00',
            $value->toISOString()
        );
    }

    public function testWithParsedBody(): void
    {
        $request1 = new ServerRequest($this->config, $this->type);
        $request2 = $request1->withParsedBody(['test' => 'value']);

        $this->assertEmpty(
            $request1->getParsedBody()
        );

        $this->assertSame(
            [
                'test' => 'value',
            ],
            $request2->getParsedBody()
        );
    }
}
