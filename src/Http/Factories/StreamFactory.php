<?php
declare(strict_types=1);

namespace Fyre\Http\Factories;

use Fyre\Http\Stream;
use InvalidArgumentException;
use Override;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Creates PSR-7 streams.
 */
class StreamFactory implements StreamFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function createStream(string $content = ''): StreamInterface
    {
        return Stream::createFromString($content);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return Stream::createFromFile($filename, $mode);
    }

    /**
     * {@inheritDoc}
     *
     * @param resource $resource The stream resource.
     */
    #[Override]
    public function createStreamFromResource($resource): StreamInterface
    {
        $stream = new Stream($resource);

        if (!$stream->isReadable()) {
            throw new InvalidArgumentException('Stream resource must be readable.');
        }

        return $stream;
    }
}
