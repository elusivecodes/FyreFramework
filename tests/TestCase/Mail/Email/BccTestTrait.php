<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

trait BccTestTrait
{
    public function testAddBcc(): void
    {
        $this->email->setBcc('test1@test.com');

        $this->assertSame(
            $this->email,
            $this->email->addBcc('test2@test.com')
        );

        $this->assertSame(
            [
                'test1@test.com' => 'test1@test.com',
                'test2@test.com' => 'test2@test.com',
            ],
            $this->email->getBcc()
        );
    }

    public function testAddBccInvalid(): void
    {
        $this->email->setBcc('test1@test.com');
        $this->email->addBcc('test2');

        $this->assertSame(
            [
                'test1@test.com' => 'test1@test.com',
            ],
            $this->email->getBcc()
        );
    }

    public function testAddBccName(): void
    {
        $this->email->setBcc('test1@test.com');
        $this->email->addBcc('test2@test.com', 'Test 2');

        $this->assertSame(
            [
                'test1@test.com' => 'test1@test.com',
                'test2@test.com' => 'Test 2',
            ],
            $this->email->getBcc()
        );
    }

    public function testHeaderBcc(): void
    {
        $this->email->setBcc('test1@test.com');

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            'test1@test.com',
            $headers['Bcc']
        );
    }

    public function testHeaderBccCharset(): void
    {
        $this->email->setCharset('iso-8859-1');
        $this->email->setBcc([
            'test1@test.com' => 'Тестовое задание',
        ]);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            '=?ISO-8859-1?B?Pz8/Pz8/Pz8gPz8/Pz8/Pw==?= <test1@test.com>',
            $headers['Bcc']
        );
    }

    public function testHeaderBccEncoding(): void
    {
        $this->email->setBcc([
            'test1@test.com' => 'Тестовое задание',
        ]);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            '=?UTF-8?B?0KLQtdGB0YLQvtCy0L7QtSDQt9Cw0LTQsNC90LjQtQ==?= <test1@test.com>',
            $headers['Bcc']
        );
    }

    public function testHeaderBccMultiple(): void
    {
        $this->email->setBcc([
            'test1@test.com' => 'Test 1',
            'test2@test.com' => 'Test 2',
        ]);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            'Test 1 <test1@test.com>, Test 2 <test2@test.com>',
            $headers['Bcc']
        );
    }

    public function testHeaderBccName(): void
    {
        $this->email->setBcc([
            'test1@test.com' => 'Test',
        ]);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            'Test <test1@test.com>',
            $headers['Bcc']
        );
    }

    public function testSetBcc(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setBcc('test1@test.com')
        );

        $this->assertSame(
            [
                'test1@test.com' => 'test1@test.com',
            ],
            $this->email->getBcc()
        );
    }

    public function testSetBccArray(): void
    {
        $this->email->setBcc([
            'test1@test.com' => 'Test 1',
        ]);

        $this->assertSame(
            [
                'test1@test.com' => 'Test 1',
            ],
            $this->email->getBcc()
        );
    }

    public function testSetBccInvalid(): void
    {
        $this->email->setBcc('test1');

        $this->assertSame(
            [],
            $this->email->getBcc()
        );
    }

    public function testSetBccMultiple(): void
    {
        $this->email->setBcc([
            'test1@test.com' => 'Test 1',
            'test2@test.com' => 'Test 2',
        ]);

        $this->assertSame(
            [
                'test1@test.com' => 'Test 1',
                'test2@test.com' => 'Test 2',
            ],
            $this->email->getBcc()
        );
    }
}
