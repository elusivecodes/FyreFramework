<?php
declare(strict_types=1);

namespace Tests\TestCase\Security\Encryption;

use Fyre\Core\Container;
use Fyre\Security\Encryption\EncryptionManager;
use Fyre\Security\Encryption\Handlers\OpenSslEncrypter;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

use function strlen;

#[RequiresPhpExtension('openssl')]
final class OpenSslTest extends TestCase
{
    use EncrypterTestTrait;

    public function testGenerateKey(): void
    {
        $key = $this->encrypter->generateKey();

        $this->assertSame(
            24,
            strlen($key)
        );
    }

    public function testInvalidCipher(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('OpenSSL cipher `invalid` is not valid.');

        new OpenSslEncrypter([
            'cipher' => 'invalid',
        ]);
    }

    public function testInvalidCipherWithoutInitializationVector(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('OpenSSL cipher `AES-128-ECB` must use an initialization vector.');

        new OpenSslEncrypter([
            'cipher' => 'AES-128-ECB',
        ]);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->encrypter = new Container()
            ->use(EncryptionManager::class)
            ->build([
                'className' => OpenSslEncrypter::class,
            ]);
    }
}
