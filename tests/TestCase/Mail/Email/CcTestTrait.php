<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

trait CcTestTrait
{
    public function testAddCc(): void
    {
        $this->email->setCc('test1@test.com');

        $this->assertSame(
            $this->email,
            $this->email->addCc('test2@test.com')
        );

        $this->assertSame(
            [
                'test1@test.com' => 'test1@test.com',
                'test2@test.com' => 'test2@test.com',
            ],
            $this->email->getCc()
        );
    }

    public function testAddCcInvalid(): void
    {
        $this->email->setCc('test1@test.com');
        $this->email->addCc('test2');

        $this->assertSame(
            [
                'test1@test.com' => 'test1@test.com',
            ],
            $this->email->getCc()
        );
    }

    public function testAddCcName(): void
    {
        $this->email->setCc('test1@test.com');
        $this->email->addCc('test2@test.com', 'Test 2');

        $this->assertSame(
            [
                'test1@test.com' => 'test1@test.com',
                'test2@test.com' => 'Test 2',
            ],
            $this->email->getCc()
        );
    }

    public function testHeaderCc(): void
    {
        $this->email->setCc('test1@test.com');

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            'test1@test.com',
            $headers['Cc']
        );
    }

    public function testHeaderCcCharset(): void
    {
        $this->email->setCharset('iso-8859-1');
        $this->email->setCc([
            'test1@test.com' => 'Тестовое задание',
        ]);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            '=?ISO-8859-1?B?Pz8/Pz8/Pz8gPz8/Pz8/Pw==?= <test1@test.com>',
            $headers['Cc']
        );
    }

    public function testHeaderCcEncoding(): void
    {
        $this->email->setCc([
            'test1@test.com' => 'Тестовое задание',
        ]);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            '=?UTF-8?B?0KLQtdGB0YLQvtCy0L7QtSDQt9Cw0LTQsNC90LjQtQ==?= <test1@test.com>',
            $headers['Cc']
        );
    }

    public function testHeaderCcMultiple(): void
    {
        $this->email->setCc([
            'test1@test.com' => 'Test 1',
            'test2@test.com' => 'Test 2',
        ]);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            'Test 1 <test1@test.com>, Test 2 <test2@test.com>',
            $headers['Cc']
        );
    }

    public function testHeaderCcName(): void
    {
        $this->email->setCc([
            'test1@test.com' => 'Test',
        ]);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            'Test <test1@test.com>',
            $headers['Cc']
        );
    }

    public function testSetCc(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setCc('test1@test.com')
        );

        $this->assertSame(
            [
                'test1@test.com' => 'test1@test.com',
            ],
            $this->email->getCc()
        );
    }

    public function testSetCcArray(): void
    {
        $this->email->setCc([
            'test1@test.com' => 'Test 1',
        ]);

        $this->assertSame(
            [
                'test1@test.com' => 'Test 1',
            ],
            $this->email->getCc()
        );
    }

    public function testSetCcInvalid(): void
    {
        $this->email->setCc('test1');

        $this->assertSame(
            [],
            $this->email->getCc()
        );
    }

    public function testSetCcMultiple(): void
    {
        $this->email->setCc([
            'test1@test.com' => 'Test 1',
            'test2@test.com' => 'Test 2',
        ]);

        $this->assertSame(
            [
                'test1@test.com' => 'Test 1',
                'test2@test.com' => 'Test 2',
            ],
            $this->email->getCc()
        );
    }
}
