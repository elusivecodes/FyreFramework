<?php
declare(strict_types=1);

namespace Fyre\Http\Factories;

use Fyre\Http\UploadedFile;
use InvalidArgumentException;
use Override;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;

use function array_key_exists;
use function array_map;
use function is_array;
use function is_int;
use function is_string;

use const UPLOAD_ERR_OK;

/**
 * Creates PSR-7 uploaded files.
 */
class UploadedFileFactory implements UploadedFileFactoryInterface
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function createUploadedFile(
        StreamInterface $stream,
        int|null $size = null,
        int $error = UPLOAD_ERR_OK,
        string|null $clientFilename = null,
        string|null $clientMediaType = null
    ): UploadedFileInterface {
        if (!$stream->isReadable()) {
            throw new InvalidArgumentException('Uploaded file stream must be readable.');
        }

        return new UploadedFile($stream, $size, $error, $clientFilename, $clientMediaType);
    }

    /**
     * Creates an UploadedFile tree from PHP file upload data.
     *
     * @param array<string, mixed> $files The PHP file upload data.
     * @return array<string, mixed> The UploadedFile tree.
     *
     * @throws InvalidArgumentException If the file upload data is invalid.
     */
    public function createUploadedFiles(array $files): array
    {
        return static::normalizeFiles($files)
            |> static::buildFiles(...);
    }

    /**
     * Builds an UploadedFile tree from normalized file upload data.
     *
     * @param array<string, mixed> $files The normalized file upload data.
     * @return array<string, mixed> The UploadedFile tree.
     */
    protected static function buildFiles(array $files): array
    {
        return array_map(
            static function(mixed $data): array|UploadedFileInterface {
                if ($data instanceof UploadedFileInterface) {
                    return $data;
                }

                if (!is_array($data)) {
                    throw new InvalidArgumentException('Uploaded file data is not valid.');
                }

                if (!array_key_exists('tmp_name', $data)) {
                    return static::buildFiles($data);
                }

                if (
                    !is_string($data['tmp_name']) ||
                    !is_int($data['size'] ?? null) ||
                    !is_int($data['error'] ?? null) ||
                    (isset($data['name']) && !is_string($data['name'])) ||
                    (isset($data['type']) && !is_string($data['type']))
                ) {
                    throw new InvalidArgumentException('Uploaded file data is not valid.');
                }

                return new UploadedFile(
                    $data['tmp_name'],
                    $data['size'],
                    $data['error'],
                    $data['name'] ?? null,
                    $data['type'] ?? null
                );
            },
            $files
        );
    }

    /**
     * Normalizes a PHP file upload field.
     *
     * @param array<string, mixed> $file The file upload field.
     * @return array<string, mixed> The normalized file upload field.
     */
    protected static function normalizeFileField(array $file): array
    {
        if (!isset($file['name']) || !is_array($file['name'])) {
            return $file;
        }

        $errors = $file['error'] ?? null;
        $sizes = $file['size'] ?? null;
        $temporaryNames = $file['tmp_name'] ?? null;
        $types = $file['type'] ?? [];

        if (
            !is_array($errors) ||
            !is_array($sizes) ||
            !is_array($temporaryNames) ||
            !is_array($types)
        ) {
            throw new InvalidArgumentException('Uploaded file data is not valid.');
        }

        $normalized = [];

        foreach ($file['name'] as $key => $value) {
            $data = [
                'name' => $value,
                'size' => $sizes[$key] ?? null,
                'error' => $errors[$key] ?? null,
                'tmp_name' => $temporaryNames[$key] ?? null,
                'type' => $types[$key] ?? null,
            ];

            $normalized[$key] = is_array($value) ?
                static::normalizeFileField($data) :
                $data;
        }

        return $normalized;
    }

    /**
     * Normalizes PHP file upload data.
     *
     * @param array<string, mixed> $files The PHP file upload data.
     * @return array<string, mixed> The normalized file upload data.
     */
    protected static function normalizeFiles(array $files): array
    {
        $results = [];

        foreach ($files as $name => $file) {
            if ($file instanceof UploadedFileInterface) {
                $results[$name] = $file;

                continue;
            }

            if (!is_array($file)) {
                throw new InvalidArgumentException('Uploaded file data is not valid.');
            }

            $results[$name] = static::normalizeFileField($file);
        }

        return $results;
    }
}
