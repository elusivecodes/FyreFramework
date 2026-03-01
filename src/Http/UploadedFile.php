<?php
declare(strict_types=1);

namespace Fyre\Http;

use Fyre\Core\Traits\DebugTrait;
use Override;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

use function move_uploaded_file;
use function sprintf;

use const UPLOAD_ERR_OK;

/**
 * Provides a PSR-7 {@see UploadedFileInterface} implementation wrapping a PHP upload
 * temporary file.
 */
class UploadedFile implements UploadedFileInterface
{
    use DebugTrait;

    protected bool $hasMoved = false;

    protected StreamInterface|null $stream = null;

    /**
     * Constructs an UploadedFile.
     *
     * @param string $file The uploaded file path.
     * @param int $size The uploaded file size.
     * @param int $error The uploaded error code.
     * @param string|null $clientFilename The client filename.
     * @param string|null $clientMediaType The client media type.
     */
    public function __construct(
        protected string $file,
        protected int $size,
        protected int $error,
        protected string|null $clientFilename = null,
        protected string|null $clientMediaType = null
    ) {}

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
     *
     * Note: This will open the underlying temp file path. Calling this after a successful
     * {@see UploadedFile::moveTo()} may fail depending on how the runtime handles moved
     * uploaded files.
     */
    #[Override]
    public function getStream(): StreamInterface
    {
        return $this->stream ??= Stream::createFromFile($this->file);
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

        if (!move_uploaded_file($this->file, $targetPath)) {
            throw new RuntimeException(sprintf(
                'Failed to move upload `%s` to `%s`.',
                $this->clientFilename ?? '',
                $targetPath
            ));
        }

        $this->hasMoved = true;
    }
}
