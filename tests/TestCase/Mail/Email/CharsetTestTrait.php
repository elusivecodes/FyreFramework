<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

trait CharsetTestTrait
{
    public function testDefaultCharset(): void
    {
        $this->assertSame(
            'utf-8',
            $this->email->getCharset()
        );
    }

    public function testSetCharset(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setCharset('iso-8859-1')
        );

        $this->assertSame(
            'iso-8859-1',
            $this->email->getCharset()
        );
    }
}
