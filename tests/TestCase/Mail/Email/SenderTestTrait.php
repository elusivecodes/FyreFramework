<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

use PHPUnit\Framework\Attributes\DataProvider;

trait SenderTestTrait
{
    /**
     * @return array<string, array{array{0: string, 1?: string}, array<string, string>}>
     */
    public static function senderAddressesProvider(): array
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
    public static function senderHeaderProvider(): array
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
    #[DataProvider('senderHeaderProvider')]
    public function testHeaderSender(array $arguments, string $expected): void
    {
        $this->email->setSender(...$arguments);

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            $expected,
            $headers['Sender']
        );
    }

    public function testHeaderSenderCharset(): void
    {
        $this->email->setCharset('iso-8859-1');
        $this->email->setSender('test1@test.com', 'Тестовое задание');

        $headers = $this->email->getFullHeaders();

        $this->assertSame(
            '=?ISO-8859-1?B?Pz8/Pz8/Pz8gPz8/Pz8/Pw==?= <test1@test.com>',
            $headers['Sender']
        );
    }

    /**
     * @param array{0: string, 1?: string} $arguments
     * @param array<string, string> $expected
     */
    #[DataProvider('senderAddressesProvider')]
    public function testSetSender(array $arguments, array $expected): void
    {
        $this->email->setSender(...$arguments);

        $this->assertArraysAreIdentical(
            $expected,
            $this->email->getSender()
        );
    }

    public function testSetSenderReturnsSelf(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setSender('test1@test.com')
        );
    }
}
