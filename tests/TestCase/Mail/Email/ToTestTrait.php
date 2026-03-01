<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

trait ToTestTrait
{
    public function testAddTo(): void
    {
        $this->email->setTo('test1@test.com');

        $this->assertSame(
            $this->email,
            $this->email->addTo('test2@test.com')
        );

        $this->assertSame(
            [
                'test1@test.com' => 'test1@test.com',
                'test2@test.com' => 'test2@test.com',
            ],
            $this->email->getTo()
        );
    }

    public function testAddToInvalid(): void
    {
        $this->email->setTo('test1@test.com');
        $this->email->addTo('test2');

        $this->assertSame(
            [
                'test1@test.com' => 'test1@test.com',
            ],
            $this->email->getTo()
        );
    }

    public function testAddToName(): void
    {
        $this->email->setTo('test1@test.com');
        $this->email->addTo('test2@test.com', 'Test 2');

        $this->assertSame(
            [
                'test1@test.com' => 'test1@test.com',
                'test2@test.com' => 'Test 2',
            ],
            $this->email->getTo()
        );
    }

    public function testHeaderTo(): void
    {
        $this->email->setTo('test1@test.com');

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            'test1@test.com',
            $headers['To']
        );
    }

    public function testHeaderToCharset(): void
    {
        $this->email->setCharset('iso-8859-1');
        $this->email->setTo([
            'test1@test.com' => 'Тестовое задание',
        ]);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            '=?ISO-8859-1?B?Pz8/Pz8/Pz8gPz8/Pz8/Pw==?= <test1@test.com>',
            $headers['To']
        );
    }

    public function testHeaderToEncoding(): void
    {
        $this->email->setTo([
            'test1@test.com' => 'Тестовое задание',
        ]);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            '=?UTF-8?B?0KLQtdGB0YLQvtCy0L7QtSDQt9Cw0LTQsNC90LjQtQ==?= <test1@test.com>',
            $headers['To']
        );
    }

    public function testHeaderToMultiple(): void
    {
        $this->email->setTo([
            'test1@test.com' => 'Test 1',
            'test2@test.com' => 'Test 2',
        ]);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            'Test 1 <test1@test.com>, Test 2 <test2@test.com>',
            $headers['To']
        );
    }

    public function testHeaderToName(): void
    {
        $this->email->setTo([
            'test1@test.com' => 'Test',
        ]);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            'Test <test1@test.com>',
            $headers['To']
        );
    }

    public function testSetTo(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setTo('test1@test.com')
        );

        $this->assertSame(
            [
                'test1@test.com' => 'test1@test.com',
            ],
            $this->email->getTo()
        );
    }

    public function testSetToArray(): void
    {
        $this->email->setTo([
            'test1@test.com' => 'Test 1',
        ]);

        $this->assertSame(
            [
                'test1@test.com' => 'Test 1',
            ],
            $this->email->getTo()
        );
    }

    public function testSetToInvalid(): void
    {
        $this->email->setTo('test1');

        $this->assertSame(
            [],
            $this->email->getTo()
        );
    }

    public function testSetToMultiple(): void
    {
        $this->email->setTo([
            'test1@test.com' => 'Test 1',
            'test2@test.com' => 'Test 2',
        ]);

        $this->assertSame(
            [
                'test1@test.com' => 'Test 1',
                'test2@test.com' => 'Test 2',
            ],
            $this->email->getTo()
        );
    }
}
