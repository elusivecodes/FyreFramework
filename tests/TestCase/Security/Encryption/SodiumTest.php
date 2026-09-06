<?php
declare(strict_types=1);

namespace Tests\TestCase\Security\Encryption;

use Fyre\Core\Container;
use Fyre\Security\Encryption\EncryptionManager;
use Fyre\Security\Encryption\Exceptions\EncryptionException;
use Fyre\Security\Encryption\Handlers\SodiumEncrypter;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

use function serialize;
use function sodium_pad;
use function strlen;

use const SODIUM_CRYPTO_SECRETBOX_KEYBYTES;
use const SODIUM_CRYPTO_SECRETBOX_MACBYTES;
use const SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;

#[RequiresPhpExtension('sodium')]
final class SodiumTest extends TestCase
{
    use EncrypterTestTrait;

    public function testAuthentication(): void
    {
        $this->expectException(EncryptionException::class);
        $this->expectExceptionMessageIs('Decryption failed.');

        $encrypted = $this->encrypter->encrypt('test', 'key');
        $encrypted[0] = $encrypted[0] === "\0" ? "\1" : "\0";

        $this->encrypter->decrypt($encrypted, 'key');
    }

    public function testDigest(): void
    {
        $encrypter = new SodiumEncrypter([
            'digest' => 'SHA3-256',
        ]);

        $encrypted = $encrypter->encrypt('test', 'key');

        $this->assertSame(
            'test',
            $encrypter->decrypt($encrypted, 'key')
        );
    }

    public function testGenerateKey(): void
    {
        $key = $this->encrypter->generateKey();

        $this->assertSame(
            SODIUM_CRYPTO_SECRETBOX_KEYBYTES,
            strlen($key)
        );
    }

    public function testInvalidBlockSize(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Sodium encrypter option `blockSize` must be greater than 0.');

        new SodiumEncrypter([
            'blockSize' => 0,
        ]);
    }

    public function testInvalidDigest(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Encryption digest `invalid` is not valid.');

        new SodiumEncrypter([
            'digest' => 'invalid',
        ]);
    }

    public function testSecretboxPayloadFormat(): void
    {
        $encrypted = $this->encrypter->encrypt('test', 'key');
        $padded = sodium_pad(serialize('test'), 16);

        $this->assertSame(
            SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + SODIUM_CRYPTO_SECRETBOX_MACBYTES + strlen($padded),
            strlen($encrypted)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->encrypter = new Container()
            ->use(EncryptionManager::class)
            ->build([
                'className' => SodiumEncrypter::class,
            ]);
    }
}
