<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\ServerRequest;

use Fyre\Http\Exceptions\BadRequestException;
use Fyre\Http\ServerRequest;
use Fyre\Utility\DateTime\Date;

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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
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
            'headers' => [
                'Content-Type' => 'application/json',
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
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'test' => 'value',
            ]),
        ]);

        $this->assertArraysAreIdentical(
            [
                'test' => 'value',
            ],
            $request->getData()
        );
    }

    public function testGetDataJsonArray(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'test' => [
                    'a' => 'value',
                ],
            ]),
        ]);

        $this->assertArraysAreIdentical(
            [
                'a' => 'value',
            ],
            $request->getData('test')
        );
    }

    public function testGetDataJsonDot(): void
    {
        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'Content-Type' => 'application/json',
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

    public function testGetDataJsonInvalid(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessageIs('The request body is not valid.');

        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => '{',
        ]);

        $request->getData();
    }

    public function testGetDataJsonScalar(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessageIs('The request body is not valid.');

        $request = new ServerRequest($this->config, $this->type, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => 'true',
        ]);

        $request->getData();
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
            Date::class,
            $value
        );

        $this->assertSame(
            '2024-12-31',
            $value->toIsoString()
        );
    }

    public function testWithParsedBody(): void
    {
        $request1 = new ServerRequest($this->config, $this->type);
        $request2 = $request1->withParsedBody(['test' => 'value']);

        $this->assertArraysAreIdentical(
            [],
            $request1->getParsedBody()
        );

        $this->assertArraysAreIdentical(
            [
                'test' => 'value',
            ],
            $request2->getParsedBody()
        );
    }
}
