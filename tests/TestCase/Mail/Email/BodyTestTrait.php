<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

use Fyre\Mail\Email;

trait BodyTestTrait
{
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
