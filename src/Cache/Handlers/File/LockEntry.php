<?php
declare(strict_types=1);

namespace Fyre\Cache\Handlers\File;

use Throwable;
use UnexpectedValueException;

use function time;
use function unserialize;

/**
 * Represents stored lock data.
 *
 * @internal
 */
final class LockEntry
{
    /**
     * Creates a LockEntry from serialized data.
     *
     * @param string $data The serialized data.
     * @return static|null The LockEntry, or null if the data is not valid.
     */
    public static function createFromString(string $data): static|null
    {
        try {
            $entry = @unserialize($data, [
                'allowed_classes' => [self::class],
            ]);
        } catch (Throwable) {
            return null;
        }

        return $entry instanceof LockEntry ?
            $entry :
            null;
    }

    /**
     * Constructs a LockEntry.
     *
     * @param int $expires The expiration timestamp.
     * @param string $owner The owner token.
     */
    public function __construct(
        protected int $expires,
        protected string $owner
    ) {}

    /**
     * Serializes the lock entry.
     *
     * @return array<string, mixed> The serialized data.
     */
    public function __serialize(): array
    {
        return [
            'expires' => $this->expires,
            'owner' => $this->owner,
        ];
    }

    /**
     * Unserializes the lock entry.
     *
     * @param array<string, mixed> $data The serialized data.
     */
    public function __unserialize(array $data): void
    {
        if (!isset($data['expires'], $data['owner'])) {
            throw new UnexpectedValueException('Lock entry data is not valid.');
        }

        $this->expires = $data['expires'];
        $this->owner = $data['owner'];
    }

    /**
     * Determines whether the lock has expired.
     *
     * @param int|null $timestamp The comparison timestamp.
     * @return bool Whether the lock has expired.
     */
    public function isExpired(int|null $timestamp = null): bool
    {
        return $this->expires <= ($timestamp ?? time());
    }

    /**
     * Determines whether the lock belongs to an owner.
     *
     * @param string $owner The owner token.
     * @return bool Whether the lock belongs to the owner.
     */
    public function isOwnedBy(string $owner): bool
    {
        return $this->owner === $owner;
    }
}
