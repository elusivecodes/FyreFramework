<?php
declare(strict_types=1);

namespace Tests\TestCase\Http\Factories;

use Fyre\Http\Factories\StreamFactory;
use Fyre\Http\Stream;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

use function assert;
use function fclose;
use function file_put_contents;
use function fopen;
use function fwrite;
use function rewind;
use function sys_get_temp_dir;
use function tempnam;
use function unlink;

final class StreamFactoryTest extends TestCase
{
    protected StreamFactory $streamFactory;

    public function testCreateStream(): void
    {
        $stream = $this->streamFactory->createStream('This is a test.');

        $this->assertInstanceOf(
            Stream::class,
            $stream
        );

        $this->assertSame(
            'This is a test.',
            $stream->getContents()
        );
    }

    public function testCreateStreamFromFile(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'stream-factory');

        assert($filePath !== false);

        file_put_contents($filePath, 'This is a test.');

        try {
            $stream = $this->streamFactory->createStreamFromFile($filePath);

            $this->assertSame(
                'This is a test.',
                $stream->getContents()
            );
        } finally {
            @unlink($filePath);
        }
    }

    public function testCreateStreamFromResource(): void
    {
        $resource = fopen('php://temp', 'r+');

        $this->assertIsResource($resource);

        fwrite($resource, 'This is a test.');
        rewind($resource);

        $stream = $this->streamFactory->createStreamFromResource($resource);

        $this->assertSame(
            'This is a test.',
            $stream->getContents()
        );
    }

    public function testCreateStreamFromResourceNotReadable(): void
    {
        $filePath = tempnam(sys_get_temp_dir(), 'stream-factory');

        assert($filePath !== false);

        $resource = fopen($filePath, 'w');

        $this->assertIsResource($resource);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Stream resource must be readable.');

        try {
            $this->streamFactory->createStreamFromResource($resource);
        } finally {
            fclose($resource);
            @unlink($filePath);
        }
    }

    #[Override]
    protected function setUp(): void
    {
        $this->streamFactory = new StreamFactory();
    }
}
