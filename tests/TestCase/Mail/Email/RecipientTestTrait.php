<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

trait RecipientTestTrait
{
    public function testRecipients(): void
    {
        $this->email->setTo('test1@test.com');
        $this->email->setCc('test2@test.com');
        $this->email->setBcc('test3@test.com');

        $this->assertSame(
            [
                'test1@test.com' => 'test1@test.com',
                'test2@test.com' => 'test2@test.com',
                'test3@test.com' => 'test3@test.com',
            ],
            $this->email->getRecipients()
        );
    }
}
