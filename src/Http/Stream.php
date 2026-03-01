<?php
declare(strict_types=1);

namespace Fyre\Http;

use Fyre\Core\Exceptions\ErrorException;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Core\Traits\StaticMacroTrait;
use Override;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Stringable;

use function assert;
use function fclose;
use function feof;
use function fopen;
use function fread;
use function fseek;
use function fstat;
use function ftell;
use function fwrite;
use function get_resource_type;
use function is_resource;
use function preg_match;
use function rewind;
use function stream_get_contents;
use function stream_get_meta_data;

use const SEEK_SET;

/**
 * Provides a PSR-7 {@see StreamInterface} implementation backed by a PHP stream resource.
 */
class Stream implements StreamInterface, Stringable
{
    use DebugTrait;
    use MacroTrait;
    use StaticMacroTrait;

    /**
     * Creates a Stream from a file path.
     *
     * @param string $filePath The file path.
     * @param string $mode The file access mode.
     * @return static The new Stream instance.
     *
     * @throws RuntimeException If the file cannot be opened.
     */
    public static function createFromFile(string $filePath, string $mode = 'r'): static
    {
        $resource = fopen($filePath, $mode);

        return new static($resource);
    }

    /**
     * Creates a Stream from a string.
     *
     * @param string $content The string content.
     * @return static The new Stream instance.
     *
     * @throws RuntimeException If the temp stream cannot be opened.
     */
    public static function createFromString(string $content = ''): static
    {
        $resource = fopen('php://temp', 'r+');

        if (is_resource($resource)) {
            fwrite($resource, $content);
            rewind($resource);
        }

        return new static($resource);
    }

    /**
     * Constructs a Stream.
     *
     * @param resource|null $resource The stream resource.
     *
     * @throws RuntimeException If the resource is not valid.
     */
    public function __construct(
        protected $resource
    ) {
        if (!is_resource($resource) || get_resource_type($resource) !== 'stream') {
            throw new RuntimeException('Invalid stream resource.');
        }
    }

    /**
     * Returns the entire contents of the stream.
     *
     * If the stream is seekable, it is rewound before reading.
     *
     * @return string The entire contents of the stream.
     */
    #[Override]
    public function __toString(): string
    {
        if (!$this->isReadable()) {
            return '';
        }

        if ($this->isSeekable()) {
            $this->rewind();
        }

        return $this->getContents();
    }

    /**
     * {@inheritDoc}
     *
     * Closes and detaches the underlying stream resource.
     *
     * @throws RuntimeException If the resource is not valid.
     */
    #[Override]
    public function close(): void
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Invalid stream resource.');
        }

        /** @var resource $resource */
        $resource = $this->detach();

        fclose($resource);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function detach(): mixed
    {
        $resource = $this->resource;

        $this->resource = null;

        return $resource;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function eof(): bool
    {
        if (!is_resource($this->resource)) {
            return true;
        }

        return feof($this->resource);
    }

    /**
     * {@inheritDoc}
     *
     * Reads from the current stream pointer to EOF.
     *
     * @throws ErrorException|RuntimeException If the resource is not readable.
     */
    #[Override]
    public function getContents(): string
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Invalid stream resource.');
        }

        if (($contents = stream_get_contents($this->resource)) === false) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $contents;
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException If the resource is not valid.
     */
    #[Override]
    public function getMetadata(string|null $key = null): mixed
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Invalid stream resource.');
        }

        $data = stream_get_meta_data($this->resource);

        return $key ?
            ($data[$key] ?? null) :
            $data;
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException If the resource is not valid.
     */
    #[Override]
    public function getSize(): int|null
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Invalid stream resource.');
        }

        $stats = fstat($this->resource);

        return $stats['size'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function isReadable(): bool
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];

        return preg_match('/[r+]/', $mode) === 1;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function isSeekable(): bool
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);

        return $meta['seekable'];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function isWritable(): bool
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);
        $mode = $meta['mode'];

        return preg_match('/[xwca+]/', $mode) === 1;
    }

    /**
     * {@inheritDoc}
     *
     * @throws ErrorException|RuntimeException If the resource is not readable.
     */
    #[Override]
    public function read(int $length): string
    {
        assert($length > 0);

        if (!is_resource($this->resource)) {
            throw new RuntimeException('Invalid stream resource.');
        }

        if (($result = @fread($this->resource, $length)) === false) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * {@inheritDoc}
     *
     * @throws ErrorException|RuntimeException If the resource is not seekable.
     */
    #[Override]
    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Invalid stream resource.');
        }

        if (($result = @fseek($this->resource, $offset, $whence)) !== 0) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws ErrorException|RuntimeException If the resource is not valid.
     */
    #[Override]
    public function tell(): int
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Invalid stream resource.');
        }

        if (($result = @ftell($this->resource)) === false) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     *
     * @throws ErrorException|RuntimeException If the resource is not writable.
     */
    #[Override]
    public function write(string $data): int
    {
        if (!is_resource($this->resource)) {
            throw new RuntimeException('Invalid stream resource.');
        }

        if (($result = @fwrite($this->resource, $data)) === false) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }

        return $result;
    }
}
