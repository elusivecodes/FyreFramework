<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers;

use DateInterval;
use DirectoryIterator;
use Fyre\Cache\Cacher;
use Fyre\Core\Attributes\SensitiveProperty;
use Fyre\Core\Attributes\SensitivePropertyArray;
use Fyre\Utility\Path;
use Override;
use RuntimeException;

use function array_key_exists;
use function chmod;
use function fclose;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function flock;
use function fopen;
use function ftruncate;
use function fwrite;
use function is_array;
use function is_dir;
use function is_numeric;
use function is_resource;
use function mkdir;
use function rewind;
use function serialize;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function stream_get_contents;
use function time;
use function unlink;
use function unserialize;

use const LOCK_EX;
use const LOCK_UN;

/**
 * Caches values on the filesystem.
 */
class FileCacher extends Cacher
{
    /**
     * @var array<string, mixed>
     */
    #[Override]
    protected static array $defaults = [
        'path' => '/tmp/cache',
        'mode' => 0640,
    ];

    /**
     * @var array<string, mixed>
     */
    #[Override]
    #[SensitivePropertyArray(['path'])]
    protected array $config;

    #[SensitiveProperty]
    protected string $path;

    /**
     * Constructs a FileCacher.
     *
     * @param array<string, mixed> $options The Cacher options.
     *
     * @throws RuntimeException If the cache path cannot be created or the prefix is invalid.
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        $this->path = Path::resolve($this->config['path']);

        if (!is_dir($this->path) && !mkdir($this->path, 0777, true)) {
            throw new RuntimeException(sprintf(
                'Folder `%s` could not be created.',
                $this->path
            ));
        }

        if ($this->config['prefix'] && str_contains($this->config['prefix'], DIRECTORY_SEPARATOR)) {
            throw new RuntimeException(sprintf(
                'Cache prefix `%s` is not valid.',
                $this->config['prefix']
            ));
        }
    }

    /**
     * {@inheritDoc}
     *
     * Note: When a prefix is configured, only matching cache files are removed.
     */
    #[Override]
    public function clear(): bool
    {
        $iterator = new DirectoryIterator($this->path);

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                continue;
            }

            if ($this->config['prefix'] && !str_starts_with($item->getBasename(), $this->config['prefix'])) {
                continue;
            }

            $filePath = $item->getPathname();
            @unlink($filePath);
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function delete(string $key): bool
    {
        $key = $this->prepareKey($key);
        $filePath = Path::join($this->path, $key);

        if (!file_exists($filePath)) {
            return false;
        }

        @unlink($filePath);

        return true;
    }

    /**
     * {@inheritDoc}
     *
     * Note: Values are stored as a serialized array containing `data` and `expires`.
     */
    #[Override]
    public function get(string $key, mixed $default = null): mixed
    {
        $key = $this->prepareKey($key);
        $filePath = Path::join($this->path, $key);

        $contents = @file_get_contents($filePath);

        if ($contents === false) {
            return $default;
        }

        $data = unserialize($contents);

        if (
            !is_array($data) ||
            !array_key_exists('data', $data) ||
            !array_key_exists('expires', $data) ||
            ($data['expires'] !== null && $data['expires'] <= time())
        ) {
            @unlink($filePath);

            return $default;
        }

        return $data['data'];
    }

    /**
     * {@inheritDoc}
     *
     * Note: Values are treated as expired when `expires` is less than or equal to the current time.
     */
    #[Override]
    public function increment(string $key, int $amount = 1): false|int
    {
        $key = $this->prepareKey($key);
        $filePath = Path::join($this->path, $key);

        $chmod = !file_exists($filePath);

        $handle = @fopen($filePath, 'c+');

        if (!is_resource($handle)) {
            return false;
        }

        @flock($handle, LOCK_EX);

        if ($chmod) {
            @chmod($filePath, $this->config['mode']);
        }

        $contents = @stream_get_contents($handle);

        if ($contents === false) {
            @flock($handle, LOCK_UN);
            @fclose($handle);

            return false;
        }

        $data = unserialize($contents);

        if ($data === false) {
            $data = [
                'data' => 0,
                'expires' => null,
            ];
        }

        if (!is_numeric($data['data'])) {
            @flock($handle, LOCK_UN);
            @fclose($handle);

            return false;
        }

        if ($data['expires'] !== null && $data['expires'] <= time()) {
            $data['data'] = 0;
            $data['expires'] = null;
        } else {
            $data['data'] = (int) $data['data'];
        }

        $data['data'] += $amount;

        @ftruncate($handle, 0);
        @rewind($handle);
        @fwrite($handle, serialize($data));
        @flock($handle, LOCK_UN);
        @fclose($handle);

        return $data['data'];
    }

    /**
     * {@inheritDoc}
     *
     * Note: Values are stored as a serialized array containing `data` and `expires`.
     */
    #[Override]
    public function set(string $key, mixed $data, DateInterval|int|null $expire = null): bool
    {
        $key = $this->prepareKey($key);
        $filePath = Path::join($this->path, $key);

        $expires = $this->getExpires($expire);

        if ($expires !== null) {
            $expires += time();
        }

        $chmod = !file_exists($filePath);

        $data = serialize([
            'data' => $data,
            'expires' => $expires,
        ]);

        if (file_put_contents($filePath, $data, LOCK_EX) === false) {
            return false;
        }

        if ($chmod) {
            @chmod($filePath, $this->config['mode']);
        }

        return true;
    }
}
