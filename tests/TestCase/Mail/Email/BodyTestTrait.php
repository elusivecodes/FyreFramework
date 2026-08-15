<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

use Fyre\Mail\Email;

use function str_repeat;

trait BodyTestTrait
{
    public function testFullBodyBothWithAttachment(): void
    {
        $this->email
            ->setBody([
                Email::TEXT => 'Text body',
                Email::HTML => '<b>HTML body</b>',
            ])
            ->setFormat(Email::BOTH)
            ->setAttachments([
                'test.txt' => [
                    'content' => 'Attachment',
                    'mimeType' => 'text/plain',
                ],
            ]);

        $body = $this->email->getFullBody();

        $this->assertContains(
            'Content-Type: multipart/alternative; boundary="alt-boundary"',
            $body
        );

        $this->assertContains(
            '--alt-boundary--',
            $body
        );
    }

    public function testFullBodyWrapHtml(): void
    {
        $tag = '<'.str_repeat('a', 1000).'>';

        $this->email
            ->setBodyHtml($tag.'Test')
            ->setFormat(Email::HTML);

        $body = $this->email->getFullBody();

        $this->assertContains($tag, $body);
        $this->assertContains('Test', $body);
    }

    public function testSetBody(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setBody([
                Email::TEXT => 'Test',
                Email::HTML => '<b>Test</b>',
            ])
        );

        $this->assertSame(
            'Test',
            $this->email->getBodyText()
        );

        $this->assertSame(
            '<b>Test</b>',
            $this->email->getBodyHtml()
        );
    }

    public function testSetBodyHtml(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setBodyHtml('<b>Test</b>')
        );

        $this->assertSame(
            '<b>Test</b>',
            $this->email->getBodyHtml()
        );
    }

    public function testSetBodyText(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setBodyText('Test')
        );

        $this->assertSame(
            'Test',
            $this->email->getBodyText()
        );
    }
}
