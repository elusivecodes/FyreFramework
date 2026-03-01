<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Email;

use Fyre\Mail\Email;
use Fyre\Mail\MailManager;
use PHPUnit\Framework\AssertionFailedError;

use function file_get_contents;

trait MailContainsAttachmentTrait
{
    public function testMailContainsAttachment(): void
    {
        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test1@test.com')
            ->setTo('test2@test.com')
            ->setSubject('Test 1')
            ->setBodyText('This is a test')
            ->addAttachments([
                'test1.jpg' => [
                    'content' => file_get_contents('tests/assets/test.jpg'),
                ],
            ])
            ->setFormat(Email::TEXT)
            ->send();

        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test2@test.com')
            ->setTo('test1@test.com')
            ->setSubject('Test 2')
            ->setBodyText('This is another test')
            ->addAttachments([
                'test2.jpg' => [
                    'content' => file_get_contents('tests/assets/test.jpg'),
                ],
            ])
            ->setFormat(Email::TEXT)
            ->send();

        $this->assertMailContainsAttachment('test2.jpg');
    }

    public function testMailContainsAttachmentAt(): void
    {
        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test1@test.com')
            ->setTo('test2@test.com')
            ->setSubject('Test 1')
            ->setBodyText('This is a test')
            ->addAttachments([
                'test1.jpg' => [
                    'content' => file_get_contents('tests/assets/test.jpg'),
                ],
            ])
            ->setFormat(Email::TEXT)
            ->send();

        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test2@test.com')
            ->setTo('test1@test.com')
            ->setSubject('Test 2')
            ->setBodyText('This is another test')
            ->addAttachments([
                'test2.jpg' => [
                    'content' => file_get_contents('tests/assets/test.jpg'),
                ],
            ])
            ->setFormat(Email::TEXT)
            ->send();

        $this->assertMailContainsAttachmentAt('test2.jpg', 2);
    }

    public function testMailContainsAttachmentAtFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that email #1 was sent with attachment "test2.jpg".');

        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test1@test.com')
            ->setTo('test2@test.com')
            ->setSubject('Test 1')
            ->setBodyText('This is a test')
            ->addAttachments([
                'test1.jpg' => [
                    'content' => file_get_contents('tests/assets/test.jpg'),
                ],
            ])
            ->setFormat(Email::TEXT)
            ->send();

        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test2@test.com')
            ->setTo('test1@test.com')
            ->setSubject('Test 2')
            ->setBodyText('This is another test')
            ->addAttachments([
                'test2.jpg' => [
                    'content' => file_get_contents('tests/assets/test.jpg'),
                ],
            ])
            ->setFormat(Email::TEXT)
            ->send();

        $this->assertMailContainsAttachmentAt('test2.jpg', 1);
    }

    public function testMailContainsAttachmentFail(): void
    {
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage('Failed asserting that an email was sent with attachment "invalid.jpg".');

        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test1@test.com')
            ->setTo('test2@test.com')
            ->setSubject('Test 2')
            ->setBodyText('This is a test')
            ->addAttachments([
                'test1.jpg' => [
                    'content' => file_get_contents('tests/assets/test.jpg'),
                ],
            ])
            ->setFormat(Email::TEXT)
            ->send();

        $this->app->use(MailManager::class)
            ->use()
            ->email()
            ->setFrom('test2@test.com')
            ->setTo('test1@test.com')
            ->setSubject('Test 2')
            ->setBodyText('This is another test')
            ->addAttachments([
                'test2.jpg' => [
                    'content' => file_get_contents('tests/assets/test.jpg'),
                ],
            ])
            ->setFormat(Email::TEXT)
            ->send();

        $this->assertMailContainsAttachment('invalid.jpg');
    }
}
