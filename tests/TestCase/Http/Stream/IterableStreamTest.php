<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Stream;

use Fyre\Http\Stream\IterableStream;
use Generator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class IterableStreamTest extends TestCase
{
    public function testClose(): void
    {
        $stream = new IterableStream(['This is a test.']);

        $stream->close();

        $this->assertTrue($stream->eof());
        $this->assertFalse($stream->isReadable());
    }

    public function testConstructor(): void
    {
        $ran = false;
        $source = static function() use (&$ran): Generator {
            $ran = true;

            yield 'This is a test.';
        };

        $stream = new IterableStream($source);

        $this->assertFalse($ran);
        $this->assertSame('T', $stream->read(1));
    }

    public function testConstructorClosureIterable(): void
    {
        $stream = new IterableStream(static fn(): iterable => ['This is a test.']);

        $this->assertSame('This is a test.', $stream->getContents());
    }

    public function testDetach(): void
    {
        $stream = new IterableStream(['This is a test.']);

        $this->assertNull($stream->detach());
        $this->assertTrue($stream->eof());
    }

    public function testEof(): void
    {
        $stream = new IterableStream(['This is a test.']);

        $this->assertFalse($stream->eof());

        $stream->read(16);

        $this->assertTrue($stream->eof());
    }

    public function testGetContents(): void
    {
        $stream = new IterableStream(['This ', 'is ', 'a test.']);

        $this->assertSame('This is a test.', $stream->getContents());
    }

    public function testGetMetadata(): void
    {
        $stream = new IterableStream(['This is a test.']);

        $this->assertArraysAreIdentical(
            [
                'seekable' => false,
            ],
            $stream->getMetadata()
        );
        $this->assertFalse($stream->getMetadata('seekable'));
        $this->assertNull($stream->getMetadata('invalid'));
    }

    public function testGetSize(): void
    {
        $stream = new IterableStream(['This is a test.']);

        $this->assertNull($stream->getSize());
    }

    public function testIsReadable(): void
    {
        $stream = new IterableStream(['This is a test.']);

        $this->assertTrue($stream->isReadable());
    }

    public function testIsSeekable(): void
    {
        $stream = new IterableStream(['This is a test.']);

        $this->assertFalse($stream->isSeekable());
    }

    public function testIsWritable(): void
    {
        $stream = new IterableStream(['This is a test.']);

        $this->assertFalse($stream->isWritable());
    }

    public function testRead(): void
    {
        $stream = new IterableStream(['This ', 'is ', 'a test.']);

        $this->assertSame('This is', $stream->read(7));
        $this->assertSame(' a test.', $stream->read(8));
    }

    public function testReadClosed(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Iterable stream is closed.');

        $stream = new IterableStream(['This is a test.']);

        $stream->close();
        $stream->read(1);
    }

    public function testReadInvalidLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Read length must be greater than 0.');

        new IterableStream(['This is a test.'])->read(0);
    }

    public function testRewind(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Iterable stream is not seekable.');

        new IterableStream(['This is a test.'])->rewind();
    }

    public function testSeek(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Iterable stream is not seekable.');

        new IterableStream(['This is a test.'])->seek(1);
    }

    public function testTell(): void
    {
        $stream = new IterableStream(['This is a test.']);

        $this->assertSame(0, $stream->tell());

        $stream->read(4);

        $this->assertSame(4, $stream->tell());
    }

    public function testToString(): void
    {
        $stream = new IterableStream(['This ', 'is ', 'a test.']);

        $this->assertSame('This is a test.', (string) $stream);
    }

    public function testWrite(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Iterable stream is not writable.');

        new IterableStream(['This is a test.'])->write('Test.');
    }
}
