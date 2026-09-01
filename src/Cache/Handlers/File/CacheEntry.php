<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers\File;

use Throwable;
use UnexpectedValueException;

use function array_key_exists;
use function time;
use function unserialize;

/**
 * Represents a stored cache entry.
 *
 * @internal
 */
final class CacheEntry
{
    /**
     * Creates a CacheEntry from serialized data.
     *
     * @param string $data The serialized data.
     * @return static|null The CacheEntry, or null if the data is not valid.
     */
    public static function createFromString(string $data): static|null
    {
        try {
            $entry = @unserialize($data);
        } catch (Throwable) {
            return null;
        }

        return $entry instanceof CacheEntry ?
            $entry :
            null;
    }

    /**
     * Constructs a CacheEntry.
     *
     * @param mixed $data The cached data.
     * @param int|null $expires The expiration timestamp.
     */
    public function __construct(
        protected mixed $data,
        protected int|null $expires
    ) {}

    /**
     * Serializes the cache entry.
     *
     * @return array<string, mixed> The serialized data.
     */
    public function __serialize(): array
    {
        return [
            'data' => $this->data,
            'expires' => $this->expires,
        ];
    }

    /**
     * Unserializes the cache entry.
     *
     * @param array<string, mixed> $data The serialized data.
     */
    public function __unserialize(array $data): void
    {
        if (
            !array_key_exists('data', $data) ||
            !array_key_exists('expires', $data)
        ) {
            throw new UnexpectedValueException('Cache entry data is not valid.');
        }

        $this->data = $data['data'];
        $this->expires = $data['expires'];
    }

    /**
     * Returns the cached data.
     *
     * @return mixed The cached data.
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Returns the expiration timestamp.
     *
     * @return int|null The expiration timestamp.
     */
    public function getExpires(): int|null
    {
        return $this->expires;
    }

    /**
     * Determines whether the cache entry has expired.
     *
     * @param int|null $timestamp The comparison timestamp.
     * @return bool Whether the cache entry has expired.
     */
    public function isExpired(int|null $timestamp = null): bool
    {
        return $this->expires !== null && $this->expires <= ($timestamp ?? time());
    }
}
