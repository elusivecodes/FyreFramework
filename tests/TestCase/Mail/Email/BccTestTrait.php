<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

use PHPUnit\Framework\Attributes\DataProvider;

trait BccTestTrait
{
    /**
     * @return array<string, array{string, string|null, array<string, string>}>
     */
    public static function addBccProvider(): array
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
    public static function headerBccProvider(): array
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
    public static function setBccProvider(): array
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
    #[DataProvider('addBccProvider')]
    public function testAddBcc(string $email, string|null $name, array $expected): void
    {
        $this->email->setBcc('test1@test.com');
        $this->email->addBcc($email, $name);

        $this->assertArraysAreIdentical(
            $expected,
            $this->email->getBcc()
        );
    }

    public function testAddBccReturnsEmail(): void
    {
        $this->email->setBcc('test1@test.com');

        $this->assertSame(
            $this->email,
            $this->email->addBcc('test2@test.com')
        );
    }

    /**
     * @param array<string, string>|string $emails
     */
    #[DataProvider('headerBccProvider')]
    public function testHeaderBcc(array|string $emails, string $expected): void
    {
        $this->email->setBcc($emails);

        $headers = $this->email->getFullHeaders(true);

        $this->assertSame(
            $expected,
            $headers['Bcc']
        );
    }

    public function testHeaderBccCharset(): void
    {
        $this->email->setCharset('iso-8859-1');
        $this->email->setBcc([
            'test1@test.com' => 'Тестовое задание',
        ]);

        $headers = $this->email->getFullHeaders(true);

        $this->assertSame(
            '=?ISO-8859-1?B?Pz8/Pz8/Pz8gPz8/Pz8/Pw==?= <test1@test.com>',
            $headers['Bcc']
        );
    }

    public function testHeaderBccExcluded(): void
    {
        $this->email->setBcc('test1@test.com');

        $this->assertArrayNotHasKey(
            'Bcc',
            $this->email->getFullHeaders()
        );
    }

    /**
     * @param array<string, string>|string $emails
     * @param array<string, string> $expected
     */
    #[DataProvider('setBccProvider')]
    public function testSetBcc(array|string $emails, array $expected): void
    {
        $this->email->setBcc($emails);

        $this->assertArraysAreIdentical(
            $expected,
            $this->email->getBcc()
        );
    }

    public function testSetBccReturnsEmail(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setBcc('test1@test.com')
        );
    }
}
