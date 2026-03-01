<?php
declare(strict_types=1);

namespace Tests\TestCase\Security\Encryption;

use Fyre\Core\Container;
use Fyre\Security\Encryption\EncryptionManager;
use Fyre\Security\Encryption\Handlers\OpenSSLEncrypter;
use Override;
use PHPUnit\Framework\TestCase;

use function strlen;

final class OpenSSLTest extends TestCase
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

    #[Override]
    protected function setUp(): void
    {
        $this->encrypter = new Container()
            ->use(EncryptionManager::class)
            ->build([
                'className' => OpenSSLEncrypter::class,
            ]);
    }
}
