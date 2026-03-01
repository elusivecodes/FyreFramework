<?php
declare(strict_types=1);

namespace Fyre\Http;

use finfo;
use InvalidArgumentException;

use function basename;
use function file_exists;
use function filesize;
use function sprintf;
use function strlen;
use function strtok;

use const FILEINFO_MIME;

/**
 * Builds a {@see ClientResponse} suitable for file downloads by setting the body to a file
 * stream and populating common headers such as `Content-Disposition` and `Content-Length`.
 */
class DownloadResponse extends ClientResponse
{
    /**
     * Creates a DownloadResponse from a file path.
     *
     * @param string $path The file path.
     * @param string|null $filename The download filename.
     * @param string|null $mimeType The MIME type.
     * @param array<string, mixed> $options The response options.
     * @return static The new DownloadResponse instance.
     */
    public static function createFromFile(string $path, string|null $filename = null, string|null $mimeType = null, array $options = []): static
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf(
                'File `%s` does not exist.',
                $path
            ));
        }

        $filename ??= basename($path);

        if (!$mimeType) {
            $finfo = new finfo(FILEINFO_MIME);
            $type = (string) $finfo->file($path);
            $mimeType = strtok($type, ';') ?: 'application/octet-stream';
        }

        $size = (int) filesize($path);

        $options = static::addHeaders($filename, $mimeType, $size, $options);
        $options['body'] = Stream::createFromFile($path);

        return new static($options);
    }

    /**
     * Creates a DownloadResponse from a string.
     *
     * This method writes the content to an in-memory temporary stream (`php://temp`)
     * which is automatically cleaned up when the stream resource is closed or
     * garbage-collected.
     *
     * @param string $content The string content.
     * @param string $filename The download filename.
     * @param string|null $mimeType The MIME type.
     * @param array<string, mixed> $options The response options.
     * @return static The new DownloadResponse instance.
     */
    public static function createFromString(string $content, string $filename, string|null $mimeType = null, array $options = []): static
    {
        if (!$mimeType) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $type = (string) $finfo->buffer($content);
            $mimeType = strtok($type, ';') ?: 'application/octet-stream';
        }

        $size = strlen($content);

        $options = static::addHeaders($filename, $mimeType, $size, $options);
        $options['body'] = Stream::createFromString($content);

        return new static($options);
    }

    /**
     * Adds common download headers to the response options.
     *
     * @param string $filename The download filename.
     * @param string $mimeType The MIME type.
     * @param int $size The content size in bytes.
     * @param array<string, mixed> $options The response options.
     * @return array<string, mixed> The updated response options.
     */
    protected static function addHeaders(string $filename, string $mimeType, int $size, array $options)
    {
        $options['headers'] ??= [];
        $options['headers']['Content-Type'] ??= $mimeType.'; charset=UTF-8';
        $options['headers']['Content-Disposition'] ??= 'attachment; filename="'.$filename.'"';
        $options['headers']['Expires'] ??= '0';
        $options['headers']['Content-Transfer-Encoding'] ??= 'binary';
        $options['headers']['Content-Length'] ??= (string) $size;
        $options['headers']['Cache-Control'] ??= ['private', 'no-transform', 'no-store', 'must-revalidate'];

        return $options;
    }
}
