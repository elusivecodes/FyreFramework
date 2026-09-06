<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

use PHPUnit\Framework\Attributes\DataProvider;

trait ReplyToTestTrait
{
    /**
     * @return array<string, array{string, string|null, array<string, string>}>
     */
    public static function addReplyToProvider(): array
    {
        return [
            'address' => [
                'test2@test.com',
                null,
                ['test1@test.com' => 'test1@test.com', 'test2@test.com' => 'test2@test.com'],
            ],
            'invalid address' => [
                'test2',
                null,
                ['test1@test.com' => 'test1@test.com'],
            ],
            'display name' => [
                'test2@test.com',
                'Test 2',
                ['test1@test.com' => 'test1@test.com', 'test2@test.com' => 'Test 2'],
            ],
        ];
    }

    /**
     * @return array<string, array{array<string, string>|string, string}>
     */
    public static function headerReplyToProvider(): array
    {
        return [
            'address' => [
                'test1@test.com',
                'test1@test.com',
            ],
            'encoded name' => [
                ['test1@test.com' => 'Тестовое задание'],
                '=?UTF-8?B?0KLQtdGB0YLQvtCy0L7QtSDQt9Cw0LTQsNC90LjQtQ==?= <test1@test.com>',
            ],
            'multiple addresses' => [
                ['test1@test.com' => 'Test 1', 'test2@test.com' => 'Test 2'],
                'Test 1 <test1@test.com>, Test 2 <test2@test.com>',
            ],
            'display name' => [
                ['test1@test.com' => 'Test'],
                'Test <test1@test.com>',
            ],
        ];
    }

    /**
     * @return array<string, array{array<string, string>|string, array<string, string>}>
     */
    public static function setReplyToProvider(): array
    {
        return [
            'address' => [
                'test1@test.com',
                ['test1@test.com' => 'test1@test.com'],
            ],
            'named address' => [
                ['test1@test.com' => 'Test 1'],
                ['test1@test.com' => 'Test 1'],
            ],
            'invalid address' => [
                'test1',
                [],
            ],
            'multiple addresses' => [
                ['test1@test.com' => 'Test 1', 'test2@test.com' => 'Test 2'],
                ['test1@test.com' => 'Test 1', 'test2@test.com' => 'Test 2'],
            ],
        ];
    }

    /**
     * @param array<string, string> $expected
     */
    #[DataProvider('addReplyToProvider')]
    public function testAddReplyTo(string $email, string|null $name, array $expected): void
    {
        $this->email->setReplyTo('test1@test.com');
        $this->email->addReplyTo($email, $name);

        $this->assertArraysAreIdentical(
            $expected,
            $this->email->getReplyTo()
        );
    }

    public function testAddReplyToReturnsEmail(): void
    {
        $this->email->setReplyTo('test1@test.com');

        $this->assertSame(
            $this->email,
            $this->email->addReplyTo('test2@test.com')
        );
    }

    /**
     * @param array<string, string>|string $emails
     */
    #[DataProvider('headerReplyToProvider')]
    public function testHeaderReplyTo(array|string $emails, string $expected): void
    {
        $this->email->setReplyTo($emails);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            $expected,
            $headers['Reply-To']
        );
    }

    public function testHeaderReplyToCharset(): void
    {
        $this->email->setCharset('iso-8859-1');
        $this->email->setReplyTo([
            'test1@test.com' => 'Тестовое задание',
        ]);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            '=?ISO-8859-1?B?Pz8/Pz8/Pz8gPz8/Pz8/Pw==?= <test1@test.com>',
            $headers['Reply-To']
        );
    }

    /**
     * @param array<string, string>|string $emails
     * @param array<string, string> $expected
     */
    #[DataProvider('setReplyToProvider')]
    public function testSetReplyTo(array|string $emails, array $expected): void
    {
        $this->email->setReplyTo($emails);

        $this->assertArraysAreIdentical(
            $expected,
            $this->email->getReplyTo()
        );
    }

    public function testSetReplyToReturnsEmail(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setReplyTo('test1@test.com')
        );
    }
}
