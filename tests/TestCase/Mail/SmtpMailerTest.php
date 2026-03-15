<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail;

use Closure;
use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Mail\Exceptions\MailException;
use Fyre\Mail\Handlers\SmtpMailer;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

use function array_shift;
use function assert;
use function base64_encode;
use function fopen;
use function str_contains;

final class SmtpMailerTest extends TestCase
{
    protected Container $container;

    protected SmtpMailer $mailer;

    /**
     * @var string[]
     */
    protected array $replies = [];

    /**
     * @var string[]
     */
    protected array $sent = [];

    public function testAuthenticate(): void
    {
        $this->replies = [
            '334 Username',
            '334 Password',
            '235 Authenticated',
        ];

        Closure::bind(function(): void {
            $this->authenticate();
        }, $this->mailer, $this->mailer)();

        $this->assertSame(
            [
                'AUTH LOGIN',
                base64_encode('user'),
                base64_encode('pass'),
            ],
            $this->sent
        );
    }

    public function testAuthenticateAlreadyAuthenticated(): void
    {
        $this->replies = [
            '503 Already authenticated',
        ];

        Closure::bind(function(): void {
            $this->socket = fopen('php://temp', 'r+');
            $this->authenticate();
        }, $this->mailer, $this->mailer)();

        $this->assertSame(
            [
                'AUTH LOGIN',
            ],
            $this->sent
        );
    }

    public function testAuthenticateInvalidReply(): void
    {
        $this->expectException(MailException::class);
        $this->expectExceptionMessage('SMTP authentication failed.');

        $this->replies = [
            '500 Error',
        ];

        Closure::bind(function(): void {
            $this->socket = fopen('php://temp', 'r+');
            $this->authenticate();
        }, $this->mailer, $this->mailer)();
    }

    public function testDestruct(): void
    {
        $this->replies = [
            '221 Bye',
        ];

        Closure::bind(function(): void {
            $this->socket = fopen('php://temp', 'r+');
            $this->__destruct();
        }, $this->mailer, $this->mailer)();

        $this->assertSame(
            [
                'QUIT',
            ],
            $this->sent
        );

        $this->assertNull(
            Closure::bind(function() {
                return $this->socket;
            }, $this->mailer, $this->mailer)()
        );
    }

    public function testEndKeepAlive(): void
    {
        $mailer = $this->getStubBuilder(SmtpMailer::class)
            ->setConstructorArgs([$this->container, [
                'host' => 'smtp.example.com',
                'username' => 'user',
                'password' => 'pass',
                'keepAlive' => true,
            ]])
            ->onlyMethods(['getData', 'sendData'])
            ->getStub();

        assert($mailer instanceof Stub);

        $sent = [];
        $replies = [
            '250 Reset',
        ];

        $mailer->method('getData')
            ->willReturnCallback(static function() use (&$replies): string {
                return array_shift($replies) ?? '';
            });

        $mailer->method('sendData')
            ->willReturnCallback(static function(string $data) use (&$sent): void {
                $sent[] = $data;
            });

        Closure::bind(function(): void {
            $this->socket = fopen('php://temp', 'r+');
            $this->end();
        }, $mailer, $mailer)();

        $this->assertSame(
            [
                'RSET',
            ],
            $sent
        );

        Closure::bind(function(): void {
            $this->socket = null;
        }, $mailer, $mailer)();
    }

    public function testSend(): void
    {
        $mailer = $this->getStubBuilder(SmtpMailer::class)
            ->setConstructorArgs([$this->container, [
                'host' => 'smtp.example.com',
                'username' => 'user',
                'password' => 'pass',
                'keepAlive' => true,
            ]])
            ->onlyMethods(['getData', 'sendData'])
            ->getStub();

        assert($mailer instanceof Stub);

        $sent = [];
        $replies = [
            '250 From',
            '250 To',
            '354 Data',
            '250 Queued',
            '250 Reset',
        ];

        $mailer->method('getData')
            ->willReturnCallback(static function() use (&$replies): string {
                return array_shift($replies) ?? '';
            });

        $mailer->method('sendData')
            ->willReturnCallback(static function(string $data) use (&$sent): void {
                $sent[] = $data;
            });

        Closure::bind(function(): void {
            $this->socket = fopen('php://temp', 'r+');
        }, $mailer, $mailer)();

        $email = $mailer->email()
            ->setFrom('from@example.com')
            ->setTo('to@example.com')
            ->setSubject('Test')
            ->setBodyText('.Test');

        $mailer->send($email);

        $this->assertSame(
            'MAIL FROM:<from@example.com>',
            $sent[0]
        );

        $this->assertSame(
            'RCPT TO:<to@example.com>',
            $sent[1]
        );

        $this->assertSame(
            'DATA',
            $sent[2]
        );

        $this->assertTrue(
            str_contains($sent[3], "\r\n\r\n..Test\r\n\r\n")
        );

        $this->assertSame(
            '.',
            $sent[4]
        );

        $this->assertSame(
            'RSET',
            $sent[5]
        );

        Closure::bind(function(): void {
            $this->socket = null;
        }, $mailer, $mailer)();
    }

    public function testSendCommandHelloAuth(): void
    {
        $mailer = $this->getStubBuilder(SmtpMailer::class)
            ->setConstructorArgs([$this->container, [
                'host' => 'smtp.example.com',
                'username' => 'user',
                'password' => 'pass',
                'client' => 'test',
                'auth' => true,
            ]])
            ->onlyMethods(['getData', 'sendData'])
            ->getStub();

        assert($mailer instanceof Stub);

        $sent = [];
        $replies = [
            '250 Hello',
        ];

        $mailer->method('getData')
            ->willReturnCallback(static function() use (&$replies): string {
                return array_shift($replies) ?? '';
            });

        $mailer->method('sendData')
            ->willReturnCallback(static function(string $data) use (&$sent): void {
                $sent[] = $data;
            });

        Closure::bind(function(): void {
            $this->socket = fopen('php://temp', 'r+');
            $this->sendCommand('hello');
        }, $mailer, $mailer)();

        $this->assertSame(
            [
                'EHLO test',
            ],
            $sent
        );

        Closure::bind(function(): void {
            $this->socket = null;
        }, $mailer, $mailer)();
    }

    public function testSendCommandInvalidCommand(): void
    {
        $this->expectException(MailException::class);
        $this->expectExceptionMessage('SMTP command `invalid` is not valid.');

        Closure::bind(function(): void {
            $this->socket = fopen('php://temp', 'r+');
            $this->sendCommand('invalid');
        }, $this->mailer, $this->mailer)();
    }

    public function testSendCommandInvalidReply(): void
    {
        $this->expectException(MailException::class);
        $this->expectExceptionMessage('SMTP invalid reply: 500 Error');

        $this->replies = [
            '500 Error',
        ];

        Closure::bind(function(): void {
            $this->socket = fopen('php://temp', 'r+');
            $this->sendCommand('data');
        }, $this->mailer, $this->mailer)();
    }

    public function testSendCommandRecipientWithDsn(): void
    {
        $mailer = $this->getStubBuilder(SmtpMailer::class)
            ->setConstructorArgs([$this->container, [
                'host' => 'smtp.example.com',
                'username' => 'user',
                'password' => 'pass',
                'dsn' => true,
            ]])
            ->onlyMethods(['getData', 'sendData'])
            ->getStub();

        assert($mailer instanceof Stub);

        $sent = [];
        $replies = [
            '250 Recipient',
        ];

        $mailer->method('getData')
            ->willReturnCallback(static function() use (&$replies): string {
                return array_shift($replies) ?? '';
            });

        $mailer->method('sendData')
            ->willReturnCallback(static function(string $data) use (&$sent): void {
                $sent[] = $data;
            });

        Closure::bind(function(): void {
            $this->socket = fopen('php://temp', 'r+');
            $this->sendCommand('to', 'to@example.com');
        }, $mailer, $mailer)();

        $this->assertSame(
            [
                'RCPT TO:<to@example.com> NOTIFY=SUCCESS,DELAY,FAILURE ORCPT=rfc822;to@example.com',
            ],
            $sent
        );

        Closure::bind(function(): void {
            $this->socket = null;
        }, $mailer, $mailer)();
    }

    public function testWakeup(): void
    {
        Closure::bind(function(): void {
            $this->socket = fopen('php://temp', 'r+');
            $this->__wakeup();
        }, $this->mailer, $this->mailer)();

        $this->assertNull(
            Closure::bind(function() {
                return $this->socket;
            }, $this->mailer, $this->mailer)()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(Config::class);

        $this->mailer = $this->getStubBuilder(SmtpMailer::class)
            ->setConstructorArgs([$this->container, [
                'host' => 'smtp.example.com',
                'username' => 'user',
                'password' => 'pass',
            ]])
            ->onlyMethods(['getData', 'sendData'])
            ->getStub();

        assert($this->mailer instanceof Stub);

        $this->mailer->method('getData')
            ->willReturnCallback(function(): string {
                return array_shift($this->replies) ?? '';
            });

        $this->mailer->method('sendData')
            ->willReturnCallback(function(string $data): void {
                $this->sent[] = $data;
            });
    }

    #[Override]
    protected function tearDown(): void
    {
        Closure::bind(function(): void {
            $this->socket = null;
        }, $this->mailer, $this->mailer)();
    }
}
