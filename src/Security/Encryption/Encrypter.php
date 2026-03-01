<?php
declare(strict_types=1);

namespace Fyre\Security\Encryption;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;

use function array_replace;
use function assert;
use function hash_hkdf;
use function hash_hmac;
use function mb_substr;

/**
 * Provides shared encryption helpers for encrypter implementations.
 *
 * Includes config handling, HKDF-based key derivation, and raw-binary HMAC helpers used by
 * concrete encrypters for integrity checks.
 */
abstract class Encrypter
{
    use DebugTrait;
    use MacroTrait;

    /**
     * @var array<string, mixed>
     */
    protected static array $defaults = [
        'digest' => 'SHA512',
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * Constructs an Encrypter.
     *
     * @param array<string, mixed> $options The Encrypter options.
     */
    public function __construct(array $options = [])
    {
        $this->config = array_replace(self::$defaults, static::$defaults, $options);
    }

    /**
     * Decrypts data.
     *
     * @param string $data The encrypted data.
     * @param string $key The encryption key.
     * @return mixed The decrypted data.
     */
    abstract public function decrypt(string $data, string $key): mixed;

    /**
     * Encrypts data.
     *
     * @param mixed $data The data to encrypt.
     * @param string $key The encryption key.
     * @return string The encrypted data.
     */
    abstract public function encrypt(mixed $data, string $key): string;

    /**
     * Generates an encryption key.
     *
     * @param int|null $length The key length.
     * @return string The encryption key.
     */
    abstract public function generateKey(int|null $length = null): string;

    /**
     * Returns the config.
     *
     * @return array<string, mixed> The config.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Generates a secret key.
     *
     * Note: This derives a key using HKDF with the configured digest algorithm.
     *
     * @param string $key The encryption key.
     * @param int $length The key length.
     * @return string The secret key.
     */
    protected function generateSecret(string $key, int $length = 0): string
    {
        assert($length >= 0);

        return hash_hkdf($this->config['digest'], $key, $length);
    }

    /**
     * Returns the HMAC.
     *
     * Note: The returned value is raw binary output.
     *
     * @param string $data The data to hash.
     * @param string $secret The secret key.
     * @return string The HMAC value.
     */
    protected function getHmac(string $data, string $secret): string
    {
        return hash_hmac($this->config['digest'], $data, $secret, true);
    }

    /**
     * Returns the HMAC length.
     *
     * Note: This assumes the configured digest is in the form `SHAxxx` (e.g. `SHA512`).
     *
     * @return int The HMAC length.
     */
    protected function getHmacLength(): int
    {
        return (int) (((float) static::substr($this->config['digest'], 3)) / 8);
    }

    /**
     * Multi-byte substr.
     *
     * Note: Uses the `8bit` encoding so offsets are applied to raw bytes.
     *
     * @param string $string The input string.
     * @param int $start The starting offset.
     * @param int|null $length The maximum length to return.
     * @return string The sliced string.
     */
    protected static function substr(string $string, int $start, int|null $length = null): string
    {
        return mb_substr($string, $start, $length, '8bit');
    }
}
