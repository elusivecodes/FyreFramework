<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

trait ReturnPathTestTrait
{
    public function testHeaderReturnPath(): void
    {
        $this->email->setReturnPath('test1@test.com');

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            'test1@test.com',
            $headers['Return-Path']
        );
    }

    public function testHeaderReturnPathCharset(): void
    {
        $this->email->setCharset('iso-8859-1');
        $this->email->setReturnPath('test1@test.com', 'Тестовое задание');

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            '=?ISO-8859-1?B?Pz8/Pz8/Pz8gPz8/Pz8/Pw==?= <test1@test.com>',
            $headers['Return-Path']
        );
    }

    public function testHeaderReturnPathEncoding(): void
    {
        $this->email->setReturnPath('test1@test.com', 'Тестовое задание');

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            '=?UTF-8?B?0KLQtdGB0YLQvtCy0L7QtSDQt9Cw0LTQsNC90LjQtQ==?= <test1@test.com>',
            $headers['Return-Path']
        );
    }

    public function testHeaderReturnPathName(): void
    {
        $this->email->setReturnPath('test1@test.com', 'Test');

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            'Test <test1@test.com>',
            $headers['Return-Path']
        );
    }

    public function testSetReturnPath(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setReturnPath('test1@test.com')
        );

        $this->assertSame(
            [
                'test1@test.com' => 'test1@test.com',
            ],
            $this->email->getReturnPath()
        );
    }

    public function testSetReturnPathInvalid(): void
    {
        $this->email->setReturnPath('test1');

        $this->assertSame(
            [],
            $this->email->getReturnPath()
        );
    }

    public function testSetReturnPathName(): void
    {
        $this->email->setReturnPath('test1@test.com', 'Test 1');

        $this->assertSame(
            [
                'test1@test.com' => 'Test 1',
            ],
            $this->email->getReturnPath()
        );
    }
}
