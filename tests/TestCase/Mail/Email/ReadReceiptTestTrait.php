<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

trait ReadReceiptTestTrait
{
    public function testHeaderReadReceipt(): void
    {
        $this->email->setReadReceipt('test1@test.com');

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            'test1@test.com',
            $headers['Disposition-Notification-To']
        );
    }

    public function testHeaderReadReceiptCharset(): void
    {
        $this->email->setCharset('iso-8859-1');
        $this->email->setReadReceipt('test1@test.com', 'Тестовое задание');

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            '=?ISO-8859-1?B?Pz8/Pz8/Pz8gPz8/Pz8/Pw==?= <test1@test.com>',
            $headers['Disposition-Notification-To']
        );
    }

    public function testHeaderReadReceiptEncoding(): void
    {
        $this->email->setReadReceipt('test1@test.com', 'Тестовое задание');

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            '=?UTF-8?B?0KLQtdGB0YLQvtCy0L7QtSDQt9Cw0LTQsNC90LjQtQ==?= <test1@test.com>',
            $headers['Disposition-Notification-To']
        );
    }

    public function testHeaderReadReceiptName(): void
    {
        $this->email->setReadReceipt('test1@test.com', 'Test');

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            'Test <test1@test.com>',
            $headers['Disposition-Notification-To']
        );
    }

    public function testSetReadReceipt(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setReadReceipt('test1@test.com')
        );

        $this->assertSame(
            [
                'test1@test.com' => 'test1@test.com',
            ],
            $this->email->getReadReceipt()
        );
    }

    public function testSetReadReceiptInvalid(): void
    {
        $this->email->setReadReceipt('test1');

        $this->assertSame(
            [],
            $this->email->getReadReceipt()
        );
    }

    public function testSetReadReceiptName(): void
    {
        $this->email->setReadReceipt('test1@test.com', 'Test 1');

        $this->assertSame(
            [
                'test1@test.com' => 'Test 1',
            ],
            $this->email->getReadReceipt()
        );
    }
}
