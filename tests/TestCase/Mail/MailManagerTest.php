<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Mail\Handlers\SendmailMailer;
use Fyre\Mail\MailManager;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class MailManagerTest extends TestCase
{
    protected MailManager $mailManager;

    public function testBuild(): void
    {
        $this->assertInstanceOf(
            SendmailMailer::class,
            $this->mailManager->build([
                'className' => SendmailMailer::class,
            ])
        );
    }

    public function testBuildInvalidHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mailer `Invalid` must extend `Fyre\Mail\Mailer`.');

        $this->mailManager->build([
            'className' => 'Invalid',
        ]);
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(MailManager::class)
        );
    }

    public function testGetConfig(): void
    {
        $this->assertSame(
            [
                'default' => [
                    'className' => SendmailMailer::class,
                ],
                'other' => [
                    'className' => SendmailMailer::class,
                ],
            ],
            $this->mailManager->getConfig()
        );
    }

    public function testGetConfigKey(): void
    {
        $this->assertSame(
            [
                'className' => SendmailMailer::class,
            ],
            $this->mailManager->getConfig('default')
        );
    }

    public function testIsLoaded(): void
    {
        $this->mailManager->use();

        $this->assertTrue(
            $this->mailManager->isLoaded()
        );
    }

    public function testIsLoadedInvalid(): void
    {
        $this->assertFalse(
            $this->mailManager->isLoaded('test')
        );
    }

    public function testIsLoadedKey(): void
    {
        $this->mailManager->use('other');

        $this->assertTrue(
            $this->mailManager->isLoaded('other')
        );
    }

    public function testSetConfig(): void
    {
        $this->assertSame(
            $this->mailManager,
            $this->mailManager->setConfig('test', [
                'className' => SendmailMailer::class,
            ])
        );

        $this->assertSame(
            [
                'className' => SendmailMailer::class,
            ],
            $this->mailManager->getConfig('test')
        );
    }

    public function testSetConfigExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mail config `default` already exists.');

        $this->mailManager->setConfig('default', [
            'className' => SendmailMailer::class,
        ]);
    }

    public function testUnload(): void
    {
        $this->mailManager->use();

        $this->assertSame(
            $this->mailManager,
            $this->mailManager->unload()
        );

        $this->assertFalse(
            $this->mailManager->isLoaded()
        );
        $this->assertFalse(
            $this->mailManager->hasConfig()
        );
    }

    public function testUnloadInvalid(): void
    {
        $this->assertSame(
            $this->mailManager,
            $this->mailManager->unload('test')
        );
    }

    public function testUnloadKey(): void
    {
        $this->mailManager->use('other');

        $this->assertSame(
            $this->mailManager,
            $this->mailManager->unload('other')
        );

        $this->assertFalse(
            $this->mailManager->isLoaded('other')
        );
        $this->assertFalse(
            $this->mailManager->hasConfig('other')
        );
    }

    public function testUse(): void
    {
        $handler1 = $this->mailManager->use();
        $handler2 = $this->mailManager->use();

        $this->assertSame($handler1, $handler2);

        $this->assertInstanceOf(
            SendmailMailer::class,
            $handler1
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(Config::class);
        $container->use(Config::class)->set('Mail', [
            'default' => [
                'className' => SendmailMailer::class,
            ],
            'other' => [
                'className' => SendmailMailer::class,
            ],
        ]);
        $this->mailManager = $container->use(MailManager::class);
    }
}
