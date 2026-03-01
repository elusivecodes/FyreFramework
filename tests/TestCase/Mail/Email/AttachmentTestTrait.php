<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

trait AttachmentTestTrait
{
    public function testAddAttachments(): void
    {
        $this->email->setAttachments([
            'test1.jpg' => [
                'file' => 'test1.jpg',
            ],
        ]);

        $this->email->addAttachments([
            'test2.jpg' => [
                'file' => 'test2.jpg',
            ],
        ]);

        $this->assertSame(
            [
                'test1.jpg' => [
                    'file' => 'test1.jpg',
                ],
                'test2.jpg' => [
                    'file' => 'test2.jpg',
                ],
            ],
            $this->email->getAttachments()
        );
    }

    public function testSetAttachments(): void
    {
        $this->email->setAttachments([
            'test.jpg' => [
                'file' => 'tests/assets/test.jpg',
            ],
        ]);

        $this->assertSame(
            [
                'test.jpg' => [
                    'file' => 'tests/assets/test.jpg',
                ],
            ],
            $this->email->getAttachments()
        );
    }
}
