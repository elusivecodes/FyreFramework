<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

use PHPUnit\Framework\Attributes\DataProvider;

trait ReturnPathTestTrait
{
    /**
     * @return array<string, array{array{0: string, 1?: string}, array<string, string>}>
     */
    public static function returnPathAddressesProvider(): array
    {
        return [
            'address only' => [
                ['test1@test.com'],
                [
                    'test1@test.com' => 'test1@test.com',
                ],
            ],
            'invalid address' => [
                ['test1'],
                [],
            ],
            'named address' => [
                ['test1@test.com', 'Test 1'],
                [
                    'test1@test.com' => 'Test 1',
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{array{0: string, 1?: string}, string}>
     */
    public static function returnPathHeaderProvider(): array
    {
        return [
            'address only' => [
                ['test1@test.com'],
                'test1@test.com',
            ],
            'encoded name' => [
                ['test1@test.com', 'Тестовое задание'],
                '=?UTF-8?B?0KLQtdGB0YLQvtCy0L7QtSDQt9Cw0LTQsNC90LjQtQ==?= <test1@test.com>',
            ],
            'plain name' => [
                ['test1@test.com', 'Test'],
                'Test <test1@test.com>',
            ],
        ];
    }

    /**
     * @param array{0: string, 1?: string} $arguments
     */
    #[DataProvider('returnPathHeaderProvider')]
    public function testHeaderReturnPath(array $arguments, string $expected): void
    {
        $this->email->setReturnPath(...$arguments);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            $expected,
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

    /**
     * @param array{0: string, 1?: string} $arguments
     * @param array<string, string> $expected
     */
    #[DataProvider('returnPathAddressesProvider')]
    public function testSetReturnPath(array $arguments, array $expected): void
    {
        $this->email->setReturnPath(...$arguments);

        $this->assertArraysAreIdentical(
            $expected,
            $this->email->getReturnPath()
        );
    }

    public function testSetReturnPathReturnsSelf(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setReturnPath('test1@test.com')
        );
    }
}
