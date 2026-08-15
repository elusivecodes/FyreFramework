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
use function base64_encode;
use function fclose;
use function fopen;
use function fwrite;
use function rewind;

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
            /** @var SmtpMailer $this */
            $this->authenticate();
        }, $this->mailer, SmtpMailer::class)();

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
            $socket = fopen('php://temp', 'r+');
            TestCase::assertIsResource($socket);

            /** @var SmtpMailer $this */
            $this->socket = $socket;
            $this->authenticate();
        }, $this->mailer, SmtpMailer::class)();

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
            $socket = fopen('php://temp', 'r+');
            TestCase::assertIsResource($socket);

            /** @var SmtpMailer $this */
            $this->socket = $socket;
            $this->authenticate();
        }, $this->mailer, SmtpMailer::class)();
    }

    public function testDefaultPort(): void
    {
        $this->assertSame(
            '25',
            $this->mailer->getConfig()['port']
        );
    }

    public function testDestruct(): void
    {
        $this->replies = [
            '221 Bye',
        ];

        Closure::bind(function(): void {
            $socket = fopen('php://temp', 'r+');
            TestCase::assertIsResource($socket);

            /** @var SmtpMailer $this */
            $this->socket = $socket;
            $this->__destruct();
        }, $this->mailer, SmtpMailer::class)();

        $this->assertSame(
            [
                'QUIT',
            ],
            $this->sent
        );

        $this->assertNull(
            Closure::bind(function(): mixed {
                /** @var SmtpMailer $this */
                return $this->socket;
            }, $this->mailer, SmtpMailer::class)()
        );
    }

    public function testEndKeepAlive(): void
    {
        /** @var SmtpMailer&Stub $mailer */
        $mailer = $this->getStubBuilder(SmtpMailer::class)
            ->setConstructorArgs([$this->container, [
                'host' => 'smtp.example.com',
                'username' => 'user',
                'password' => 'pass',
                'keepAlive' => true,
            ]])
            ->onlyMethods(['getData', 'sendData'])
            ->getStub();

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
            $socket = fopen('php://temp', 'r+');
            TestCase::assertIsResource($socket);

            /** @var SmtpMailer $this */
            $this->socket = $socket;
            $this->end();
        }, $mailer, SmtpMailer::class)();

        $this->assertSame(
            [
                'RSET',
            ],
            $sent
        );

        Closure::bind(function(): void {
            /** @var SmtpMailer $this */
            $this->socket = null;
        }, $mailer, SmtpMailer::class)();
    }

    public function testGetData(): void
    {
        $mailer = new SmtpMailer($this->container);

        $data = Closure::bind(function(): string {
            $socket = fopen('php://temp', 'r+');
            TestCase::assertIsResource($socket);

            fwrite($socket, "250-First line\r\n250 Final line\r\nNext line\r\n");
            rewind($socket);
            $this->socket = $socket;

            try {
                return $this->getData();
            } finally {
                fclose($socket);
                $this->socket = null;
            }
        }, $mailer, SmtpMailer::class)();

        $this->assertSame(
            "250-First line\r\n250 Final line\r\n",
            $data
        );
    }

    public function testGetDataConnectionClosed(): void
    {
        $this->expectException(MailException::class);
        $this->expectExceptionMessage('SMTP connection closed unexpectedly.');

        $mailer = new SmtpMailer($this->container);

        Closure::bind(function(): void {
            $socket = fopen('php://temp', 'r+');
            TestCase::assertIsResource($socket);

            /** @var SmtpMailer $this */
            $this->socket = $socket;

            try {
                $this->getData();
            } finally {
                fclose($socket);
                $this->socket = null;
            }
        }, $mailer, SmtpMailer::class)();
    }

    public function testSend(): void
    {
        /** @var SmtpMailer&Stub $mailer */
        $mailer = $this->getStubBuilder(SmtpMailer::class)
            ->setConstructorArgs([$this->container, [
                'host' => 'smtp.example.com',
                'username' => 'user',
                'password' => 'pass',
                'keepAlive' => true,
            ]])
            ->onlyMethods(['getData', 'sendData'])
            ->getStub();

        $sent = [];
        $replies = [
            '250 From',
            '250 To',
            '250 Bcc',
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
            $socket = fopen('php://temp', 'r+');
            TestCase::assertIsResource($socket);

            /** @var SmtpMailer $this */
            $this->socket = $socket;
        }, $mailer, SmtpMailer::class)();

        $email = $mailer->email()
            ->setFrom('from@example.com')
            ->setTo('to@example.com')
            ->setBcc('bcc@example.com')
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
            'RCPT TO:<bcc@example.com>',
            $sent[2]
        );

        $this->assertSame(
            'DATA',
            $sent[3]
        );

        $this->assertStringContainsString(
            "\r\n\r\n..Test\r\n\r\n",
            $sent[4]
        );

        $this->assertStringNotContainsString(
            'Bcc:',
            $sent[4]
        );

        $this->assertSame(
            '.',
            $sent[5]
        );

        $this->assertSame(
            'RSET',
            $sent[6]
        );

        Closure::bind(function(): void {
            /** @var SmtpMailer $this */
            $this->socket = null;
        }, $mailer, SmtpMailer::class)();
    }

    public function testSendCommandHelloAuth(): void
    {
        /** @var SmtpMailer&Stub $mailer */
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
            $socket = fopen('php://temp', 'r+');
            TestCase::assertIsResource($socket);

            /** @var SmtpMailer $this */
            $this->socket = $socket;
            $this->sendCommand('hello');
        }, $mailer, SmtpMailer::class)();

        $this->assertSame(
            [
                'EHLO test',
            ],
            $sent
        );

        Closure::bind(function(): void {
            /** @var SmtpMailer $this */
            $this->socket = null;
        }, $mailer, SmtpMailer::class)();
    }

    public function testSendCommandInvalidCommand(): void
    {
        $this->expectException(MailException::class);
        $this->expectExceptionMessage('SMTP command `invalid` is not valid.');

        Closure::bind(function(): void {
            $socket = fopen('php://temp', 'r+');
            TestCase::assertIsResource($socket);

            /** @var SmtpMailer $this */
            $this->socket = $socket;
            $this->sendCommand('invalid');
        }, $this->mailer, SmtpMailer::class)();
    }

    public function testSendCommandInvalidReply(): void
    {
        $this->expectException(MailException::class);
        $this->expectExceptionMessage('SMTP invalid reply: 500 Error');

        $this->replies = [
            '500 Error',
        ];

        Closure::bind(function(): void {
            $socket = fopen('php://temp', 'r+');
            TestCase::assertIsResource($socket);

            /** @var SmtpMailer $this */
            $this->socket = $socket;
            $this->sendCommand('data');
        }, $this->mailer, SmtpMailer::class)();
    }

    public function testSendCommandRecipientWithDsn(): void
    {
        /** @var SmtpMailer&Stub $mailer */
        $mailer = $this->getStubBuilder(SmtpMailer::class)
            ->setConstructorArgs([$this->container, [
                'host' => 'smtp.example.com',
                'username' => 'user',
                'password' => 'pass',
                'dsn' => true,
            ]])
            ->onlyMethods(['getData', 'sendData'])
            ->getStub();

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
            $socket = fopen('php://temp', 'r+');
            TestCase::assertIsResource($socket);

            /** @var SmtpMailer $this */
            $this->socket = $socket;
            $this->sendCommand('to', 'to@example.com');
        }, $mailer, SmtpMailer::class)();

        $this->assertSame(
            [
                'RCPT TO:<to@example.com> NOTIFY=SUCCESS,DELAY,FAILURE ORCPT=rfc822;to@example.com',
            ],
            $sent
        );

        Closure::bind(function(): void {
            /** @var SmtpMailer $this */
            $this->socket = null;
        }, $mailer, SmtpMailer::class)();
    }

    public function testWakeup(): void
    {
        Closure::bind(function(): void {
            $socket = fopen('php://temp', 'r+');
            TestCase::assertIsResource($socket);

            /** @var SmtpMailer $this */
            $this->socket = $socket;
            $this->__wakeup();
        }, $this->mailer, SmtpMailer::class)();

        $this->assertNull(
            Closure::bind(function(): mixed {
                /** @var SmtpMailer $this */
                return $this->socket;
            }, $this->mailer, SmtpMailer::class)()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->container = new Container();
        $this->container->singleton(Config::class);

        /** @var SmtpMailer&Stub $mailer */
        $mailer = $this->getStubBuilder(SmtpMailer::class)
            ->setConstructorArgs([$this->container, [
                'host' => 'smtp.example.com',
                'username' => 'user',
                'password' => 'pass',
            ]])
            ->onlyMethods(['getData', 'sendData'])
            ->getStub();

        $this->mailer = $mailer;

        $mailer->method('getData')
            ->willReturnCallback(function(): string {
                return array_shift($this->replies) ?? '';
            });

        $mailer->method('sendData')
            ->willReturnCallback(function(string $data): void {
                $this->sent[] = $data;
            });
    }

    #[Override]
    protected function tearDown(): void
    {
        Closure::bind(function(): void {
            /** @var SmtpMailer $this */
            $this->socket = null;
        }, $this->mailer, SmtpMailer::class)();
    }
}
