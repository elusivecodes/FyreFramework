<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail;

use Closure;
use Fyre\Core\Container;
use Fyre\Mail\Email;
use Fyre\Mail\Handlers\DebugMailer;
use Fyre\Mail\MailManager;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

use function file_get_contents;

final class DebugTest extends TestCase
{
    protected DebugMailer $mailer;

    /**
     * @return array<string, array{Closure(): array<string, mixed>, string}>
     */
    public static function attachmentProvider(): array
    {
        return [
            'file' => [
                static fn(): array => ['file' => 'tests/assets/test.jpg'],
                '',
            ],
            'content' => [
                static fn(): array => ['content' => file_get_contents('tests/assets/test.jpg')],
                '',
            ],
            'inline' => [
                static fn(): array => [
                    'file' => 'tests/assets/test.jpg',
                    'contentId' => '1234',
                ],
                '<img src="cid:1234">',
            ],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function headerProvider(): array
    {
        return [
            'from' => ['From', 'test2@test.com'],
            'to' => ['To', 'test1@test.com'],
            'subject' => ['Subject', 'Test'],
            'mime version' => ['MIME-Version', '1.0'],
            'transfer encoding' => ['Content-Transfer-Encoding', '8bit'],
        ];
    }

    public function testDebug(): void
    {
        $data = $this->mailer->__debugInfo();

        $this->assertArraysAreIdentical(
            [
                '[class]' => DebugMailer::class,
                'config' => [
                    'charset' => 'utf-8',
                    'client' => null,
                    'className' => DebugMailer::class,
                ],
                'container' => '[Fyre\Core\Container]',
                'sentEmails' => [],
            ],
            $data
        );
    }

    /**
     * @param Closure(): array<string, mixed> $createAttachment
     */
    #[DataProvider('attachmentProvider')]
    public function testMailSendAttachmentBoundary(Closure $createAttachment, string $bodyHtml): void
    {
        $email = $this->mailer->email()
            ->setTo('test1@test.com')
            ->setFrom('test2@test.com')
            ->setSubject('Test')
            ->addAttachments([
                'test.jpg' => $createAttachment(),
            ])
            ->setFormat(Email::HTML)
            ->setBodyHtml($bodyHtml);
        $email->send();

        $boundary = $email->getBoundary();
        $sentEmail = $this->mailer->getSentEmails()[0] ?? [];

        $this->assertStringStartsWith(
            '--'.$boundary."\r\n",
            $sentEmail['body'] ?? ''
        );
    }

    /**
     * @param Closure(): array<string, mixed> $createAttachment
     */
    #[DataProvider('attachmentProvider')]
    public function testMailSendAttachmentContentType(Closure $createAttachment, string $bodyHtml): void
    {
        $email = $this->mailer->email()
            ->setTo('test1@test.com')
            ->setFrom('test2@test.com')
            ->setSubject('Test')
            ->addAttachments([
                'test.jpg' => $createAttachment(),
            ])
            ->setFormat(Email::HTML)
            ->setBodyHtml($bodyHtml);
        $email->send();

        $boundary = $email->getBoundary();
        $sentEmail = $this->mailer->getSentEmails()[0] ?? [];

        $this->assertSame(
            'multipart/mixed; boundary="'.$boundary.'"',
            $sentEmail['headers']['Content-Type'] ?? ''
        );
    }

    public function testMailSendAttachmentInline(): void
    {
        $email = $this->mailer->email()
            ->setTo('test1@test.com')
            ->setFrom('test2@test.com')
            ->setSubject('Test')
            ->addAttachments([
                'test.jpg' => [
                    'file' => 'tests/assets/test.jpg',
                    'contentId' => '1234',
                ],
            ])
            ->setFormat(Email::HTML)
            ->setBodyHtml('<img src="cid:1234">');
        $email->send();

        $sentEmail = $this->mailer->getSentEmails()[0] ?? [];

        $this->assertStringContainsString(
            '<img src="cid:1234">',
            $sentEmail['body'] ?? ''
        );
    }

    public function testMailSendAttachmentInvalid(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIs('Email attachment `missing.txt` is not valid.');

        $this->mailer->email()
            ->setTo('test1@test.com')
            ->setFrom('test2@test.com')
            ->addAttachments([
                'missing.txt' => [
                    'file' => 'tests/assets/missing.txt',
                ],
            ])
            ->send();
    }

    public function testMailSendBodyHtml(): void
    {
        $this->mailer->email()
            ->setTo('test1@test.com')
            ->setFrom('test2@test.com')
            ->setSubject('Test')
            ->setFormat(Email::HTML)
            ->setBodyHtml('<b>This is a test</b>')
            ->send();

        $sentEmail = $this->mailer->getSentEmails()[0] ?? [];

        $this->assertSame(
            '<b>This is a test</b>'."\r\n\r\n",
            $sentEmail['body'] ?? ''
        );
    }

    public function testMailSendBodyText(): void
    {
        $this->mailer->email()
            ->setTo('test1@test.com')
            ->setFrom('test2@test.com')
            ->setSubject('Test')
            ->setBodyText('This is a test')
            ->send();

        $sentEmail = $this->mailer->getSentEmails()[0] ?? [];

        $this->assertSame(
            'This is a test'."\r\n\r\n",
            $sentEmail['body'] ?? ''
        );
    }

    public function testMailSendContentTypeHtml(): void
    {
        $this->mailer->email()
            ->setTo('test1@test.com')
            ->setFrom('test2@test.com')
            ->setSubject('Test')
            ->setFormat(Email::HTML)
            ->setBodyHtml('<b>This is a test</b>')
            ->send();

        $sentEmail = $this->mailer->getSentEmails()[0] ?? [];

        $this->assertSame(
            'text/html; charset=utf-8',
            $sentEmail['headers']['Content-Type'] ?? ''
        );
    }

    public function testMailSendContentTypeText(): void
    {
        $this->mailer->email()
            ->setTo('test1@test.com')
            ->setFrom('test2@test.com')
            ->setSubject('Test')
            ->setBodyText('This is a test')
            ->send();

        $sentEmail = $this->mailer->getSentEmails()[0] ?? [];

        $this->assertSame(
            'text/plain; charset=utf-8',
            $sentEmail['headers']['Content-Type'] ?? ''
        );
    }

    #[DataProvider('headerProvider')]
    public function testMailSendHeader(string $header, string $expected): void
    {
        $this->mailer->email()
            ->setTo('test1@test.com')
            ->setFrom('test2@test.com')
            ->setSubject('Test')
            ->setBodyText('This is a test')
            ->send();

        $sentEmail = $this->mailer->getSentEmails()[0] ?? [];

        $this->assertSame($expected, $sentEmail['headers'][$header] ?? '');
    }

    #[Override]
    protected function setUp(): void
    {
        $mailer = new Container()
            ->use(MailManager::class)
            ->build([
                'className' => DebugMailer::class,
            ]);

        $this->assertInstanceOf(
            DebugMailer::class,
            $mailer
        );

        $this->mailer = $mailer;
    }
}
