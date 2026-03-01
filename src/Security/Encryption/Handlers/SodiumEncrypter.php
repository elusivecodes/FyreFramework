<?php
declare(strict_types=1);

namespace Fyre\Security\Encryption\Handlers;

use Fyre\Security\Encryption\Encrypter;
use Fyre\Security\Encryption\Exceptions\EncryptionException;
use Override;

use function assert;
use function hash_equals;
use function mb_strlen;
use function random_bytes;
use function serialize;
use function sodium_crypto_secretbox;
use function sodium_crypto_secretbox_open;
use function sodium_memzero;
use function sodium_pad;
use function sodium_unpad;
use function unserialize;

use const SODIUM_CRYPTO_SECRETBOX_KEYBYTES;
use const SODIUM_CRYPTO_SECRETBOX_MACBYTES;
use const SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;

/**
 * Encrypter implementation using libsodium with HMAC integrity checks.
 *
 * Data is serialized, padded, encrypted using secretbox, and authenticated using an HMAC
 * derived from the encryption key.
 */
class SodiumEncrypter extends Encrypter
{
    /**
     * @var array<string, mixed>
     */
    #[Override]
    protected static array $defaults = [
        'blockSize' => 16,
    ];

    /**
     * {@inheritDoc}
     *
     * @throws EncryptionException If decryption fails.
     */
    #[Override]
    public function decrypt(string $data, string $key): mixed
    {
        if (mb_strlen($data, '8bit') < SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES) {
            throw new EncryptionException('Decryption failed.');
        }

        $secret = $this->generateSecret($key, SODIUM_CRYPTO_SECRETBOX_KEYBYTES);

        $hmacLength = (int) $this->getHmacLength();
        $hmacKey = static::substr($data, 0, $hmacLength);
        $data = static::substr($data, $hmacLength);

        $hmacCalc = $this->getHmac($data, $secret);

        if (!hash_equals($hmacKey, $hmacCalc)) {
            throw new EncryptionException('Decryption failed.');
        }

        $nonce = static::substr($data, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = static::substr($data, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $data = sodium_crypto_secretbox_open($cipher, $nonce, $secret);

        if ($data === false) {
            throw new EncryptionException('Decryption failed.');
        }

        $data = sodium_unpad($data, $this->config['blockSize']);

        sodium_memzero($cipher);
        sodium_memzero($key);

        return unserialize($data);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function encrypt(mixed $data, string $key): string
    {
        $secret = $this->generateSecret($key, SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
        $nonce = $this->generateKey(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $data = serialize($data);

        $data = sodium_pad($data, $this->config['blockSize']);

        $cypher = sodium_crypto_secretbox($data, $nonce, $secret);

        sodium_memzero($data);
        sodium_memzero($key);

        $result = $nonce.$cypher;

        $hmacKey = $this->getHmac($result, $secret);

        return $hmacKey.$result;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function generateKey(int|null $length = null): string
    {
        $length ??= SODIUM_CRYPTO_SECRETBOX_KEYBYTES;

        assert($length > 0);

        return random_bytes($length);
    }
}
