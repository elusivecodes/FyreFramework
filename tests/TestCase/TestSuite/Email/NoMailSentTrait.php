<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Email;

use Fyre\Mail\Email;
use Fyre\Mail\MailManager;
use PHPUnit\Framework\AssertionFailedError;

trait NoMailSentTrait
{
    public function testNoMailSent(): void
    {
        $this->assertNoMailSent();
    }

    public function testNoMailSentFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that no emails were sent.');

        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test1@test.com')
            ->setTo('test2@test.com')
            ->setSubject('Test 1')
            ->setBodyText('This is a test')
            ->setFormat(Email::TEXT)
            ->send();

        $this->assertNoMailSent();
    }
}
