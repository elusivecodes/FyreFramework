<?php
declare(strict_types=1);

namespace Tests\TestCase\Security\Encryption;

use Fyre\Core\Container;
use Fyre\Security\Encryption\EncryptionManager;
use Fyre\Security\Encryption\Handlers\SodiumEncrypter;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

use function strlen;

use const SODIUM_CRYPTO_SECRETBOX_KEYBYTES;

final class SodiumTest extends TestCase
{
    use EncrypterTestTrait;

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
        $this->expectExceptionMessage('Sodium encrypter option `blockSize` must be greater than 0.');

        new SodiumEncrypter([
            'blockSize' => 0,
        ]);
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
