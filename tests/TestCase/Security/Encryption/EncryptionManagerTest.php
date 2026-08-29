<?php
declare(strict_types=1);

namespace Tests\TestCase\Security\Encryption;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Security\Encryption\Encrypter;
use Fyre\Security\Encryption\EncryptionManager;
use Fyre\Security\Encryption\Handlers\OpenSslEncrypter;
use Fyre\Security\Encryption\Handlers\SodiumEncrypter;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class EncryptionManagerTest extends TestCase
{
    protected EncryptionManager $encryptionManager;

    public function testClear(): void
    {
        $this->encryptionManager->use();

        $this->encryptionManager->clear();

        $this->assertFalse($this->encryptionManager->isLoaded());
        $this->assertFalse($this->encryptionManager->hasConfig());
    }

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
        $config = $this->encryptionManager->getConfig();

        $this->assertIsArray($config);
        $this->assertArraysAreIdentical(
            [
                'default' => [
                    'className' => SodiumEncrypter::class,
                ],
                'openssl' => [
                    'className' => OpenSslEncrypter::class,
                ],
            ],
            $config
        );
    }

    public function testGetConfigKey(): void
    {
        $config = $this->encryptionManager->getConfig('openssl');

        $this->assertIsArray($config);
        $this->assertArraysAreIdentical(
            [
                'className' => OpenSslEncrypter::class,
            ],
            $config
        );
    }

    public function testIsLoaded(): void
    {
        $this->encryptionManager->use();

        $this->assertTrue(
            $this->encryptionManager->isLoaded()
        );
    }

    public function testIsLoadedInvalid(): void
    {
        $this->assertFalse(
            $this->encryptionManager->isLoaded('test')
        );
    }

    public function testIsLoadedKey(): void
    {
        $this->encryptionManager->use('openssl');

        $this->assertTrue(
            $this->encryptionManager->isLoaded('openssl')
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
            $this->encryptionManager,
            $this->encryptionManager->setConfig('test', [
                'className' => SodiumEncrypter::class,
            ])
        );

        $config = $this->encryptionManager->getConfig('test');

        $this->assertIsArray($config);
        $this->assertArraysAreIdentical(
            [
                'className' => SodiumEncrypter::class,
            ],
            $config
        );
    }

    public function testSetConfigExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Encryption config `default` already exists.');

        $this->encryptionManager->setConfig('default', [
            'className' => SodiumEncrypter::class,
        ]);
    }

    public function testUnload(): void
    {
        $this->encryptionManager->use();

        $this->assertSame(
            $this->encryptionManager,
            $this->encryptionManager->unload()
        );

        $this->assertFalse(
            $this->encryptionManager->isLoaded()
        );
        $this->assertFalse(
            $this->encryptionManager->hasConfig()
        );
    }

    public function testUnloadInvalid(): void
    {
        $this->assertSame(
            $this->encryptionManager,
            $this->encryptionManager->unload('test')
        );
    }

    public function testUnloadKey(): void
    {
        $this->encryptionManager->use('openssl');

        $this->assertSame(
            $this->encryptionManager,
            $this->encryptionManager->unload('openssl')
        );

        $this->assertFalse(
            $this->encryptionManager->isLoaded('openssl')
        );
        $this->assertFalse(
            $this->encryptionManager->hasConfig('openssl')
        );
    }

    public function testUse(): void
    {
        $handler1 = $this->encryptionManager->use();
        $handler2 = $this->encryptionManager->use();

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
                'className' => OpenSslEncrypter::class,
            ],
        ]);
        $this->encryptionManager = $container->use(EncryptionManager::class);
    }
}
