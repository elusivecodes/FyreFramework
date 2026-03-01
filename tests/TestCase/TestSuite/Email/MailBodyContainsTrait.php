<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Email;

use Fyre\Mail\Email;
use Fyre\Mail\MailManager;
use PHPUnit\Framework\AssertionFailedError;

trait MailBodyContainsTrait
{
    public function testMailContainsText(): void
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

        $this->assertMailContainsText('another test');
    }

    public function testMailContainsTextAt(): void
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

        $this->assertMailContainsTextAt('another test', 2);
    }

    public function testMailContainsTextAtFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that email #1 text body contains \'another test\'.');

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

        $this->assertMailContainsTextAt('another test', 1);
    }

    public function testMailContainsTextFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that an email text body contains \'invalid\'.');

        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test1@test.com')
            ->setTo('test2@test.com')
            ->setSubject('Test 2')
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

        $this->assertMailContainsText('invalid');
    }
}
