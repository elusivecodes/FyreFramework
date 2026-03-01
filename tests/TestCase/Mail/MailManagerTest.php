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
    protected MailManager $mail;

    public function testBuild(): void
    {
        $this->assertInstanceOf(
            SendmailMailer::class,
            $this->mail->build([
                'className' => SendmailMailer::class,
            ])
        );
    }

    public function testBuildInvalidHandler(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mailer `Invalid` must extend `Fyre\Mail\Mailer`.');

        $this->mail->build([
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
            $this->mail->getConfig()
        );
    }

    public function testGetConfigKey(): void
    {
        $this->assertSame(
            [
                'className' => SendmailMailer::class,
            ],
            $this->mail->getConfig('default')
        );
    }

    public function testIsLoaded(): void
    {
        $this->mail->use();

        $this->assertTrue(
            $this->mail->isLoaded()
        );
    }

    public function testIsLoadedInvalid(): void
    {
        $this->assertFalse(
            $this->mail->isLoaded('test')
        );
    }

    public function testIsLoadedKey(): void
    {
        $this->mail->use('other');

        $this->assertTrue(
            $this->mail->isLoaded('other')
        );
    }

    public function testSetConfig(): void
    {
        $this->assertSame(
            $this->mail,
            $this->mail->setConfig('test', [
                'className' => SendmailMailer::class,
            ])
        );

        $this->assertSame(
            [
                'className' => SendmailMailer::class,
            ],
            $this->mail->getConfig('test')
        );
    }

    public function testSetConfigExists(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mail config `default` already exists.');

        $this->mail->setConfig('default', [
            'className' => SendmailMailer::class,
        ]);
    }

    public function testUnload(): void
    {
        $this->mail->use();

        $this->assertSame(
            $this->mail,
            $this->mail->unload()
        );

        $this->assertFalse(
            $this->mail->isLoaded()
        );
        $this->assertFalse(
            $this->mail->hasConfig()
        );
    }

    public function testUnloadInvalid(): void
    {
        $this->assertSame(
            $this->mail,
            $this->mail->unload('test')
        );
    }

    public function testUnloadKey(): void
    {
        $this->mail->use('other');

        $this->assertSame(
            $this->mail,
            $this->mail->unload('other')
        );

        $this->assertFalse(
            $this->mail->isLoaded('other')
        );
        $this->assertFalse(
            $this->mail->hasConfig('other')
        );
    }

    public function testUse(): void
    {
        $handler1 = $this->mail->use();
        $handler2 = $this->mail->use();

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
        $this->mail = $container->use(MailManager::class);
    }
}
