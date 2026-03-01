<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Mail\Handlers\SmtpMailer;
use Fyre\Mail\Mailer;
use Fyre\Mail\MailManager;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class MailerTest extends TestCase
{
    protected MailManager $mailer;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Mailer::class)
        );
    }

    public function testGetClient(): void
    {
        $this->assertSame(
            'test',
            $this->mailer->build([
                'client' => 'test',
                'className' => SmtpMailer::class,
            ])->getClient()
        );
    }

    public function testInvalidConfig(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Mailer `` must extend `Fyre\Mail\Mailer`.');

        $this->mailer->use('invalid');
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Mailer::class)
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->mailer = new Container()
            ->use(MailManager::class);
    }
}
