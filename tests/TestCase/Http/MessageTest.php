<?php
declare(strict_types=1);

namespace Tests\TestCase\Http;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Http\Message;
use Fyre\Http\Stream;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class MessageTest extends TestCase
{
    protected Message $message;

    public function testConstructor(): void
    {
        $message = new Message([
            'body' => 'test',
            'headers' => [
                'test' => 'value',
            ],
            'protocolVersion' => '2.0',
        ]);

        $body = $message->getBody();

        $this->assertInstanceOf(
            Stream::class,
            $body
        );

        $this->assertSame(
            'test',
            $body->getContents()
        );

        $this->assertSame(
            [
                'value',
            ],
            $message->getHeader('test')
        );

        $this->assertSame(
            '2.0',
            $message->getProtocolVersion()
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Message::class)
        );
    }

    public function testGetHeader(): void
    {
        $message = new Message([
            'headers' => [
                'test' => 'value',
            ],
        ]);

        $this->assertSame(
            [
                'value',
            ],
            $message->getHeader('test')
        );
    }

    public function testGetHeaderInvalid(): void
    {
        $message = new Message();

        $this->assertSame(
            [],
            $message->getHeader('invalid')
        );
    }

    public function testGetHeaderLine(): void
    {
        $message = new Message([
            'headers' => [
                'test' => 'value',
            ],
        ]);

        $this->assertSame(
            'value',
            $message->getHeaderLine('test')
        );
    }

    public function testGetHeaderLineInvalid(): void
    {
        $message = new Message();

        $this->assertSame(
            '',
            $message->getHeaderLine('test')
        );
    }

    public function testGetHeaderLineMultiple(): void
    {
        $message = new Message([
            'headers' => [
                'test' => ['value1', 'value2'],
            ],
        ]);

        $this->assertSame(
            'value1, value2',
            $message->getHeaderLine('test')
        );
    }

    public function testGetHeaders(): void
    {
        $message = new Message([
            'headers' => [
                'test1' => 'value',
                'test2' => 'value',
            ],
        ]);

        $this->assertSame(
            [
                'test1' => [
                    'value',
                ],
                'test2' => [
                    'value',
                ],
            ],
            $message->getHeaders()
        );
    }

    public function testGetProtocolVersionDefault(): void
    {
        $message = new Message();

        $this->assertSame(
            '1.1',
            $message->getProtocolVersion()
        );
    }

    public function testHasHeaderTrue(): void
    {
        $message = new Message([
            'headers' => [
                'test' => 'value',
            ],
        ]);

        $this->assertTrue(
            $message->hasHeader('test')
        );
    }

    public function testHasHeaderTrueFalse(): void
    {
        $message = new Message();

        $this->assertFalse(
            $message->hasHeader('invalid')
        );
    }

    public function testWithAddedHeader(): void
    {
        $message1 = new Message([
            'headers' => [
                'test' => 'value',
            ],
        ]);
        $message2 = $message1->withAddedHeader('test', 'other');

        $this->assertNotSame(
            $message1,
            $message2
        );

        $this->assertSame(
            [
                'value',
                'other',
            ],
            $message2->getHeader('test')
        );
    }

    public function testWithAddedHeaderEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Header value cannot be empty.');

        $message1 = new Message([
            'headers' => [
                'test' => 'value',
            ],
        ]);
        $message1->withAddedHeader('test', []);
    }

    public function testWithAddedHeaderInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Header value `\x00` is not valid.");

        $message1 = new Message([
            'headers' => [
                'test' => 'value',
            ],
        ]);
        $message1->withAddedHeader('test', "\x00");
    }

    public function testWithAddedHeaderNew(): void
    {
        $message1 = new Message();
        $message2 = $message1->withAddedHeader('test', 'other');

        $this->assertFalse(
            $message1->hasHeader('test')
        );

        $this->assertSame(
            [
                'other',
            ],
            $message2->getHeader('test')
        );
    }

    public function testWithBody(): void
    {
        $message1 = new Message();
        $message2 = $message1->withBody(Stream::createFromString('test'));

        $this->assertNotSame(
            $message1,
            $message2
        );

        $this->assertSame(
            'test',
            $message2->getBody()->getContents()
        );
    }

    public function testWithHeader(): void
    {
        $message1 = new Message([
            'headers' => [
                'test' => 'value1',
            ],
        ]);
        $message2 = $message1->withHeader('test', 'value2');

        $this->assertNotSame(
            $message1,
            $message2
        );

        $this->assertSame(
            [
                'value2',
            ],
            $message2->getHeader('test')
        );
    }

    public function testWithHeaderArray(): void
    {
        $message1 = new Message();
        $message2 = $message1->withHeader('test', ['first', 'other']);

        $this->assertSame(
            [
                'first',
                'other',
            ],
            $message2->getHeader('test')
        );
    }

    public function testWithHeaderEmpty(): void
    {
        $message1 = new Message();
        $message2 = $message1->withHeader('test', '');

        $this->assertSame(
            [''],
            $message2->getHeader('test')
        );
    }

    public function testWithHeaderInvalidName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Header name `x:test` is not valid.');

        $message1 = new Message();
        $message2 = $message1->withHeader('x:test', 'value');

        $this->assertSame(
            [''],
            $message2->getHeader('test')
        );
    }

    public function testWithHeaderInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Header value `\x00` is not valid.");

        $message1 = new Message();
        $message2 = $message1->withHeader('test', "\x00");

        $this->assertSame(
            [''],
            $message2->getHeader('test')
        );
    }

    public function testWithoutHeader(): void
    {
        $message1 = new Message([
            'headers' => [
                'test' => 'value',
            ],
        ]);
        $message2 = $message1->withoutHeader('test');

        $this->assertTrue(
            $message1->hasHeader('test')
        );

        $this->assertFalse(
            $message2->hasHeader('test')
        );
    }

    public function testWithProtocolVersion(): void
    {
        $message1 = new Message();
        $message2 = $message1->withProtocolVersion('2.0');

        $this->assertNotSame(
            $message1,
            $message2
        );

        $this->assertSame(
            '2.0',
            $message2->getProtocolVersion()
        );
    }

    public function testWithProtocolVersionInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Protocol version `2.1` is not valid.');

        $message = new Message();
        $message->withProtocolVersion('2.1');
    }
}
