<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail;

use Fyre\Core\Container;
use Fyre\Mail\Email;
use Fyre\Mail\Handlers\SmtpMailer;
use Fyre\Mail\Mailer;
use Fyre\Mail\MailManager;
use Override;
use PHPUnit\Framework\TestCase;

use function file_get_contents;
use function getenv;

final class SmtpTest extends TestCase
{
    protected static Mailer $mailer;

    public function testDebug(): void
    {
        $data = self::$mailer->__debugInfo();

        $this->assertSame(
            [
                '[class]' => SmtpMailer::class,
                'config' => [
                    'charset' => 'utf-8',
                    'client' => null,
                    'host' => '[*****]',
                    'username' => '',
                    'password' => '',
                    'port' => '[*****]',
                    'auth' => '0',
                    'tls' => '0',
                    'dsn' => false,
                    'keepAlive' => true,
                    'className' => SmtpMailer::class,
                ],
                'container' => '[Fyre\Core\Container]',
                'socket' => null,
            ],
            $data
        );
    }

    public function testMailSend(): void
    {
        $this->expectNotToPerformAssertions();

        $mailTo = getenv('MAIL_TO');
        $mailFrom = getenv('MAIL_FROM');

        if (!$mailTo || !$mailFrom) {
            return;
        }

        self::$mailer->email()
            ->setTo($mailTo)
            ->setFrom($mailFrom)
            ->setSubject('Test')
            ->setBodyText('This is a test')
            ->send();
    }

    public function testMailSendAttachment(): void
    {
        $this->expectNotToPerformAssertions();

        $mailTo = getenv('MAIL_TO');
        $mailFrom = getenv('MAIL_FROM');

        if (!$mailTo || !$mailFrom) {
            return;
        }

        self::$mailer->email()
            ->setTo($mailTo)
            ->setFrom($mailFrom)
            ->setSubject('Test')
            ->addAttachments([
                'test.jpg' => [
                    'file' => 'tests/assets/test.jpg',
                ],
            ])
            ->setFormat(Email::HTML)
            ->send();
    }

    public function testMailSendAttachmentContent(): void
    {
        $this->expectNotToPerformAssertions();

        $mailTo = getenv('MAIL_TO');
        $mailFrom = getenv('MAIL_FROM');

        if (!$mailTo || !$mailFrom) {
            return;
        }

        self::$mailer->email()
            ->setTo($mailTo)
            ->setFrom($mailFrom)
            ->setSubject('Test')
            ->addAttachments([
                'test.jpg' => [
                    'content' => file_get_contents('tests/assets/test.jpg'),
                ],
            ])
            ->setFormat(Email::HTML)
            ->send();
    }

    public function testMailSendAttachmentInline(): void
    {
        $this->expectNotToPerformAssertions();

        $mailTo = getenv('MAIL_TO');
        $mailFrom = getenv('MAIL_FROM');

        if (!$mailTo || !$mailFrom) {
            return;
        }

        self::$mailer->email()
            ->setTo($mailTo)
            ->setFrom($mailFrom)
            ->setSubject('Test')
            ->addAttachments([
                'test.jpg' => [
                    'file' => 'tests/assets/test.jpg',
                    'contentId' => '1234',
                ],
            ])
            ->setFormat(Email::HTML)
            ->setBodyHtml('<img src="cid:1234">')
            ->send();
    }

    public function testMailSendHtml(): void
    {
        $this->expectNotToPerformAssertions();

        $mailTo = getenv('MAIL_TO');
        $mailFrom = getenv('MAIL_FROM');

        if (!$mailTo || !$mailFrom) {
            return;
        }

        self::$mailer->email()
            ->setTo($mailTo)
            ->setFrom($mailFrom)
            ->setSubject('Test')
            ->setFormat(Email::HTML)
            ->setBodyHtml('<b>This is a test</b>')
            ->send();
    }

    #[Override]
    public static function setUpBeforeClass(): void
    {
        self::$mailer = new Container()
            ->use(MailManager::class)
            ->build([
                'className' => SmtpMailer::class,
                'host' => getenv('SMTP_HOST'),
                'port' => getenv('SMTP_PORT'),
                'username' => getenv('SMTP_USERNAME'),
                'password' => getenv('SMTP_PASSWORD'),
                'auth' => getenv('SMTP_AUTH'),
                'tls' => getenv('SMTP_TLS'),
                'keepAlive' => true,
            ]);
    }
}
