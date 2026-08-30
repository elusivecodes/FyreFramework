<?php
declare(strict_types=1);

namespace Fyre\Http;

use Fyre\Core\Traits\DebugTrait;
use Override;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use Throwable;

use function bin2hex;
use function is_uploaded_file;
use function move_uploaded_file;
use function random_bytes;
use function rename;
use function sprintf;
use function strlen;
use function substr;
use function unlink;

use const UPLOAD_ERR_OK;

/**
 * Provides a PSR-7 {@see UploadedFileInterface} implementation wrapping a PHP upload
 * temporary file.
 */
class UploadedFile implements UploadedFileInterface
{
    use DebugTrait;

    protected string|null $file = null;

    protected bool $hasMoved = false;

    protected int|null $size;

    protected StreamInterface|null $stream = null;

    /**
     * Constructs an UploadedFile.
     *
     * @param StreamInterface|string $source The uploaded file path or stream.
     * @param int|null $size The uploaded file size.
     * @param int $error The uploaded error code.
     * @param string|null $clientFilename The client filename.
     * @param string|null $clientMediaType The client media type.
     */
    public function __construct(
        StreamInterface|string $source,
        int|null $size = null,
        protected int $error = UPLOAD_ERR_OK,
        protected string|null $clientFilename = null,
        protected string|null $clientMediaType = null
    ) {
        if ($source instanceof StreamInterface) {
            $this->stream = $source;
            $this->size = $size ?? $source->getSize();
        } else {
            $this->file = $source;
            $this->size = $size;
        }
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getClientFilename(): string|null
    {
        return $this->clientFilename;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getClientMediaType(): string|null
    {
        return $this->clientMediaType;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getSize(): int|null
    {
        return $this->size;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getStream(): StreamInterface
    {
        if ($this->hasMoved) {
            throw new RuntimeException(sprintf(
                'Upload already moved: %s',
                $this->clientFilename ?? ''
            ));
        }

        if ($this->stream) {
            return $this->stream;
        }

        if ($this->file === null) {
            throw new RuntimeException('Upload stream is not available.');
        }

        return $this->stream = Stream::createFromFile($this->file);
    }

    /**
     * {@inheritDoc}
     *
     * This method can only be called once.
     *
     * @throws RuntimeException If the upload is invalid, or cannot be moved.
     */
    #[Override]
    public function moveTo(string $targetPath): void
    {
        if ($this->hasMoved) {
            throw new RuntimeException(sprintf(
                'Upload already moved: %s',
                $this->clientFilename ?? ''
            ));
        }

        if ($this->error !== UPLOAD_ERR_OK) {
            throw new RuntimeException(sprintf(
                'Upload is not valid: %s',
                $this->clientFilename ?? ''
            ));
        }

        if ($this->file === null) {
            $this->moveStream($targetPath);
            $this->hasMoved = true;

            return;
        }

        if ($this->stream) {
            $this->stream->close();
            $this->stream = null;
        }

        $moved = is_uploaded_file($this->file) ?
            move_uploaded_file($this->file, $targetPath) :
            @rename($this->file, $targetPath);

        if (!$moved) {
            throw new RuntimeException(sprintf(
                'Failed to move upload `%s` to `%s`.',
                $this->clientFilename ?? '',
                $targetPath
            ));
        }

        $this->hasMoved = true;
    }

    /**
     * Moves a stream-backed upload.
     *
     * @param string $targetPath The target path.
     */
    protected function moveStream(string $targetPath): void
    {
        $source = $this->getStream();
        $target = null;
        $temporaryPath = null;

        try {
            $suffix = random_bytes(8) |> bin2hex(...);
            $temporaryPath = $targetPath.'.'.$suffix.'.tmp';
            $target = Stream::createFromFile($temporaryPath, 'x');

            if ($source->isSeekable()) {
                $source->rewind();
            }

            while (!$source->eof()) {
                $data = $source->read(8192);

                if ($data === '') {
                    break;
                }

                $length = strlen($data);
                $written = 0;
                while ($written < $length) {
                    $result = substr($data, $written)
                        |> $target->write(...);

                    if ($result === 0) {
                        throw new RuntimeException();
                    }

                    $written += $result;
                }
            }

            $target->close();
            $target = null;

            if (!@rename($temporaryPath, $targetPath)) {
                throw new RuntimeException();
            }

            $temporaryPath = null;
            $source->close();
            $this->stream = null;
        } catch (Throwable $exception) {
            if ($target !== null) {
                try {
                    $target->close();
                } catch (Throwable) {
                }
            }

            if ($temporaryPath !== null) {
                @unlink($temporaryPath);
            }

            throw new RuntimeException(sprintf(
                'Failed to move upload `%s` to `%s`.',
                $this->clientFilename ?? '',
                $targetPath
            ), 0, $exception);
        }
    }
}
