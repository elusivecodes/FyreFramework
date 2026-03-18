<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail;

use Fyre\Core\Container;
use Fyre\Mail\Exceptions\MailException;
use Fyre\Mail\Handlers\SendmailMailer;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function assert;

final class SendmailMailerTest extends TestCase
{
    protected Container $container;

    /**
     * @var SendmailMailer&Stub
     */
    protected SendmailMailer $mailer;

    public function testDebug(): void
    {
        $mailer = new SendmailMailer($this->container, [
            'className' => SendmailMailer::class,
        ]);

        $this->assertSame(
            [
                '[class]' => SendmailMailer::class,
                'config' => [
                    'charset' => 'utf-8',
                    'client' => null,
                    'className' => SendmailMailer::class,
                ],
                'container' => '[Fyre\Core\Container]',
            ],
            $mailer->__debugInfo()
        );
    }

    public function testSend(): void
    {
        $captured = [];

        $this->mailer->method('sendMail')
            ->willReturnCallback(static function(string $to, string $subject, string $body, array $headers) use (&$captured): bool {
                $captured = [
                    'to' => $to,
                    'subject' => $subject,
                    'body' => $body,
                    'headers' => $headers,
                ];

                return true;
            });

        $email = $this->mailer->email()
            ->setTo('to@example.com')
            ->setFrom('from@example.com')
            ->setSubject('Test')
            ->setBodyText('This is a test');

        $expectedHeaders = $email->getFullHeaders();
        $expectedBody = $email->getFullBodyString();
        $expectedTo = $expectedHeaders['To'] ?? '';
        $expectedSubject = $expectedHeaders['Subject'] ?? '';

        unset($expectedHeaders['To']);
        unset($expectedHeaders['Subject']);

        $this->mailer->send($email);

        $this->assertSame(
            $expectedTo,
            $captured['to'] ?? ''
        );

        $this->assertSame(
            $expectedSubject,
            $captured['subject'] ?? ''
        );

        $this->assertSame(
            $expectedBody,
            $captured['body'] ?? ''
        );

        $this->assertSame(
            $expectedHeaders,
            $captured['headers'] ?? []
        );
    }

    public function testSendFailure(): void
    {
        $this->mailer->method('sendMail')
            ->willReturn(false);

        $email = $this->mailer->email()
            ->setTo('to@example.com')
            ->setFrom('from@example.com')
            ->setSubject('Test')
            ->setBodyText('This is a test');

        $this->expectException(RuntimeException::class);

        $this->mailer->send($email);
    }

    public function testSendRequiresRecipient(): void
    {
        $this->expectException(MailException::class);
        $this->expectExceptionMessage('Email sending must have a valid recipient.');

        $this->mailer->send(
            $this->mailer->email()
                ->setFrom('from@example.com')
                ->setSubject('Test')
                ->setBodyText('This is a test')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();

        $this->mailer = $this->getStubBuilder(SendmailMailer::class)
            ->setConstructorArgs([$this->container, []])
            ->onlyMethods(['sendMail'])
            ->getStub();

        assert($this->mailer instanceof Stub);
    }
}
