<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Email;

use Fyre\Mail\Email;
use Fyre\Mail\MailManager;
use PHPUnit\Framework\AssertionFailedError;

trait MailSentToTrait
{
    public function testMailSentTo(): void
    {
        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test1@test.com')
            ->setTo('test2@test.com')
            ->setSubject('Test 1')
            ->setBodyText('This is a test')
            ->setFormat(Email::TEXT)
            ->send();

        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test2@test.com')
            ->setTo('test1@test.com')
            ->setSubject('Test 2')
            ->setBodyText('This is another test')
            ->setFormat(Email::TEXT)
            ->send();

        $this->assertMailSentTo('test2@test.com');
    }

    public function testMailSentToAt(): void
    {
        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test1@test.com')
            ->setTo('test2@test.com')
            ->setSubject('Test 1')
            ->setBodyText('This is a test')
            ->setFormat(Email::TEXT)
            ->send();

        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test2@test.com')
            ->setTo('test1@test.com')
            ->setSubject('Test 2')
            ->setBodyText('This is another test')
            ->setFormat(Email::TEXT)
            ->send();

        $this->assertMailSentToAt('test1@test.com', 2);
    }

    public function testMailSentToAtFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that email #1 was sent to "test1@test.com".');

        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test1@test.com')
            ->setTo('test2@test.com')
            ->setSubject('Test')
            ->setBodyText('This is a test')
            ->setFormat(Email::TEXT)
            ->send();

        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test2@test.com')
            ->setTo('test1@test.com')
            ->setSubject('Test 2')
            ->setBodyText('This is another test')
            ->setFormat(Email::TEXT)
            ->send();

        $this->assertMailSentToAt('test1@test.com', 1);
    }

    public function testMailSentToFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that an email was sent to "invalid@test.com".');

        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test1@test.com')
            ->setTo('test2@test.com')
            ->setSubject('Test')
            ->setBodyText('This is a test')
            ->setFormat(Email::TEXT)
            ->send();

        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test1@test.com')
            ->setTo('test1@test.com')
            ->setSubject('Test 2')
            ->setBodyText('This is another test')
            ->setFormat(Email::TEXT)
            ->send();

        $this->assertMailSentTo('invalid@test.com');
    }
}
