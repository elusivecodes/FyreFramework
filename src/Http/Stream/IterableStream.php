<?php
declare(strict_types=1);

namespace Fyre\Http\Stream;

use Closure;
use Generator;
use Override;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Stringable;
use Throwable;

use function assert;
use function implode;
use function is_string;
use function strlen;
use function substr;

/**
 * Provides a read-only, non-seekable stream backed by an iterable of string chunks.
 */
class IterableStream implements StreamInterface, Stringable
{
    protected bool $advance = false;

    protected string $buffer = '';

    protected bool $closed = false;

    protected bool $complete = false;

    /**
     * @var Generator<mixed, string, mixed, void>|null
     */
    protected Generator|null $iterator;

    protected int $position = 0;

    /**
     * Constructs an IterableStream.
     *
     * @param (Closure(): iterable<string>)|iterable<string> $source The iterable source or factory.
     */
    public function __construct(Closure|iterable $source)
    {
        if ($source instanceof Closure) {
            $source = $source();
        }

        if (!($source instanceof Generator)) {
            $source = (static function() use ($source): Generator {
                yield from $source;
            })();
        }

        $this->iterator = $source;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function __toString(): string
    {
        try {
            return $this->getContents();
        } catch (Throwable) {
            return '';
        }
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function close(): void
    {
        $this->buffer = '';
        $this->closed = true;
        $this->complete = true;
        $this->iterator = null;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function detach(): mixed
    {
        $this->close();

        return null;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function eof(): bool
    {
        return $this->closed || ($this->complete && $this->buffer === '');
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getContents(): string
    {
        if ($this->closed) {
            throw new RuntimeException('Iterable stream is closed.');
        }

        $contents = [];

        while (!$this->eof()) {
            $contents[] = $this->read(8192);
        }

        return implode('', $contents);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getMetadata(string|null $key = null): mixed
    {
        return match ($key) {
            null => ['seekable' => false],
            'seekable' => false,
            default => null,
        };
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getSize(): int|null
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function isReadable(): bool
    {
        return !$this->closed;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function isSeekable(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function read(int $length): string
    {
        assert($length > 0);

        if ($this->closed) {
            throw new RuntimeException('Iterable stream is closed.');
        }

        while (strlen($this->buffer) < $length && !$this->complete) {
            $this->readChunk();
        }

        $result = substr($this->buffer, 0, $length);
        $resultLength = strlen($result);
        $this->buffer = substr($this->buffer, $resultLength);
        $this->position += $resultLength;

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function rewind(): void
    {
        throw new RuntimeException('Iterable stream is not seekable.');
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        throw new RuntimeException('Iterable stream is not seekable.');
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function tell(): int
    {
        if ($this->closed) {
            throw new RuntimeException('Iterable stream is closed.');
        }

        return $this->position;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function write(string $data): int
    {
        throw new RuntimeException('Iterable stream is not writable.');
    }

    /**
     * Reads the next chunk into the buffer.
     *
     * @throws RuntimeException If a chunk is not a string.
     */
    protected function readChunk(): void
    {
        if (!$this->iterator) {
            $this->complete = true;

            return;
        }

        if ($this->advance) {
            $this->iterator->next();
            $this->advance = false;
        }

        if (!$this->iterator->valid()) {
            $this->complete = true;

            return;
        }

        $chunk = $this->iterator->current();

        if (!is_string($chunk)) {
            throw new RuntimeException('Iterable stream chunks must be strings.');
        }

        $this->buffer .= $chunk;
        $this->advance = true;
    }
}
