<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Email;

use Fyre\Mail\Email;
use Fyre\Mail\MailManager;
use PHPUnit\Framework\AssertionFailedError;

trait MailCountTrait
{
    public function testMailCount(): void
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

        $this->assertMailCount(2);
    }

    public function testMailCountFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that actual sent emails 1 matches expected count 2.');

        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test1@test.com')
            ->setTo('test2@test.com')
            ->setSubject('Test 1')
            ->setBodyText('This is a test')
            ->setFormat(Email::TEXT)
            ->send();

        $this->assertMailCount(2);
    }
}
