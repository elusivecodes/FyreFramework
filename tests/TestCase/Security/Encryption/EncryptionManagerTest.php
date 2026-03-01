<?php
declare(strict_types=1);

namespace Tests\TestCase\Security\Encryption;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Security\Encryption\Encrypter;
use Fyre\Security\Encryption\EncryptionManager;
use Fyre\Security\Encryption\Handlers\OpenSSLEncrypter;
use Fyre\Security\Encryption\Handlers\SodiumEncrypter;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class EncryptionManagerTest extends TestCase
{
    protected EncryptionManager $encryption;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(EncryptionManager::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Encrypter::class)
        );
    }

    public function testGetConfig(): void
    {
        $this->assertSame(
            [
                'default' => [
                    'className' => SodiumEncrypter::class,
                ],
                'openssl' => [
                    'className' => OpenSSLEncrypter::class,
                ],
            ],
            $this->encryption->getConfig()
        );
    }

    public function testGetConfigKey(): void
    {
        $this->assertSame(
            [
                'className' => OpenSSLEncrypter::class,
            ],
            $this->encryption->getConfig('openssl')
        );
    }

    public function testIsLoaded(): void
    {
        $this->encryption->use();

        $this->assertTrue(
            $this->encryption->isLoaded()
        );
    }

    public function testIsLoadedInvalid(): void
    {
        $this->assertFalse(
            $this->encryption->isLoaded('test')
        );
    }

    public function testIsLoadedKey(): void
    {
        $this->encryption->use('openssl');

        $this->assertTrue(
            $this->encryption->isLoaded('openssl')
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Encrypter::class)
        );
    }

    public function testSetConfig(): void
    {
        $this->assertSame(
            $this->encryption,
            $this->encryption->setConfig('test', [
                'className' => SodiumEncrypter::class,
            ])
        );

        $this->assertSame(
            [
                'className' => SodiumEncrypter::class,
            ],
            $this->encryption->getConfig('test')
        );
    }

    public function testSetConfigExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Encryption config `default` already exists.');

        $this->encryption->setConfig('default', [
            'className' => SodiumEncrypter::class,
        ]);
    }

    public function testUnload(): void
    {
        $this->encryption->use();

        $this->assertSame(
            $this->encryption,
            $this->encryption->unload()
        );

        $this->assertFalse(
            $this->encryption->isLoaded()
        );
        $this->assertFalse(
            $this->encryption->hasConfig()
        );
    }

    public function testUnloadInvalid(): void
    {
        $this->assertSame(
            $this->encryption,
            $this->encryption->unload('test')
        );
    }

    public function testUnloadKey(): void
    {
        $this->encryption->use('openssl');

        $this->assertSame(
            $this->encryption,
            $this->encryption->unload('openssl')
        );

        $this->assertFalse(
            $this->encryption->isLoaded('openssl')
        );
        $this->assertFalse(
            $this->encryption->hasConfig('openssl')
        );
    }

    public function testUse(): void
    {
        $handler1 = $this->encryption->use();
        $handler2 = $this->encryption->use();

        $this->assertSame($handler1, $handler2);

        $this->assertInstanceOf(
            SodiumEncrypter::class,
            $handler1
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Config::class);
        $container->use(Config::class)->set('Encryption', [
            'default' => [
                'className' => SodiumEncrypter::class,
            ],
            'openssl' => [
                'className' => OpenSSLEncrypter::class,
            ],
        ]);
        $this->encryption = $container->use(EncryptionManager::class);
    }
}
