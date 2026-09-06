<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

use PHPUnit\Framework\Attributes\DataProvider;

trait CcTestTrait
{
    /**
     * @return array<string, array{string, string|null, array<string, string>}>
     */
    public static function addCcProvider(): array
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
    public static function headerCcProvider(): array
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
    public static function setCcProvider(): array
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
    #[DataProvider('addCcProvider')]
    public function testAddCc(string $email, string|null $name, array $expected): void
    {
        $this->email->setCc('test1@test.com');
        $this->email->addCc($email, $name);

        $this->assertArraysAreIdentical(
            $expected,
            $this->email->getCc()
        );
    }

    public function testAddCcReturnsEmail(): void
    {
        $this->email->setCc('test1@test.com');

        $this->assertSame(
            $this->email,
            $this->email->addCc('test2@test.com')
        );
    }

    /**
     * @param array<string, string>|string $emails
     */
    #[DataProvider('headerCcProvider')]
    public function testHeaderCc(array|string $emails, string $expected): void
    {
        $this->email->setCc($emails);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            $expected,
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

    /**
     * @param array<string, string>|string $emails
     * @param array<string, string> $expected
     */
    #[DataProvider('setCcProvider')]
    public function testSetCc(array|string $emails, array $expected): void
    {
        $this->email->setCc($emails);

        $this->assertArraysAreIdentical(
            $expected,
            $this->email->getCc()
        );
    }

    public function testSetCcReturnsEmail(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setCc('test1@test.com')
        );
    }
}
