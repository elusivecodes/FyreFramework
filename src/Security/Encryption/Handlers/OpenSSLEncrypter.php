<?php
declare(strict_types=1);

namespace Fyre\Security\Encryption\Handlers;

use Fyre\Security\Encryption\Encrypter;
use Fyre\Security\Encryption\Exceptions\EncryptionException;
use Override;

use function hash_equals;
use function openssl_cipher_iv_length;
use function openssl_decrypt;
use function openssl_encrypt;
use function openssl_random_pseudo_bytes;
use function serialize;
use function unserialize;

use const OPENSSL_RAW_DATA;

/**
 * Encrypter implementation using OpenSSL with HMAC integrity checks.
 *
 * Data is serialized for transport, encrypted with the configured cipher, and authenticated
 * using an HMAC derived from the encryption key.
 */
class OpenSSLEncrypter extends Encrypter
{
    /**
     * @var array<string, mixed>
     */
    #[Override]
    protected static array $defaults = [
        'cipher' => 'AES-256-CTR',
    ];

    /**
     * {@inheritDoc}
     *
     * @throws EncryptionException If decryption fails.
     */
    #[Override]
    public function decrypt(string $data, string $key): mixed
    {
        $secret = $this->generateSecret($key);

        $hmacLength = (int) $this->getHmacLength();
        $hmacKey = static::substr($data, 0, $hmacLength);
        $data = static::substr($data, $hmacLength);

        $hmacCalc = $this->getHmac($data, $secret);

        if (!hash_equals($hmacKey, $hmacCalc)) {
            throw new EncryptionException('Decryption failed.');
        }

        $ivSize = $this->getCipherLength();
        $iv = static::substr($data, 0, $ivSize);
        $data = static::substr($data, $ivSize);

        $data = (string) openssl_decrypt($data, $this->config['cipher'], $secret, OPENSSL_RAW_DATA, $iv);

        return unserialize($data);
    }

    /**
     * {@inheritDoc}
     *
     * @throws EncryptionException If encryption fails.
     */
    #[Override]
    public function encrypt(mixed $data, string $key): string
    {
        $secret = $this->generateSecret($key);
        $iv = $this->getCipherLength() |> $this->generateKey(...);

        $data = serialize($data);

        $data = openssl_encrypt($data, $this->config['cipher'], $secret, OPENSSL_RAW_DATA, $iv);

        if ($data === false) {
            throw new EncryptionException('Encryption failed.');
        }

        $result = $iv.$data;

        $hmacKey = $this->getHmac($result, $secret);

        return $hmacKey.$result;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function generateKey(int|null $length = null): string
    {
        $key = openssl_random_pseudo_bytes($length ?? 24, $secure);

        if (!$secure) {
            return $this->generateKey($length);
        }

        return $key;
    }

    /**
     * Returns the cipher length.
     *
     * @return int The cipher length.
     */
    protected function getCipherLength(): int
    {
        return (int) openssl_cipher_iv_length($this->config['cipher']);
    }
}
