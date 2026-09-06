<?php
declare(strict_types=1);

namespace Tests\TestCase\Mail\Email;

use Fyre\Mail\Email;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

trait FormatTestTrait
{
    /**
     * @return array<string, array{string}>
     */
    public static function formatProvider(): array
    {
        return [
            'text' => [Email::TEXT],
            'html' => [Email::HTML],
            'both' => [Email::BOTH],
        ];
    }

    public function testDefaultFormat(): void
    {
        $this->assertSame(
            Email::TEXT,
            $this->email->getFormat()
        );
    }

    #[DataProvider('formatProvider')]
    public function testSetFormat(string $format): void
    {
        $this->email->setFormat($format);

        $this->assertSame(
            $format,
            $this->email->getFormat()
        );
    }

    public function testSetFormatInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Email format `invalid` is not valid.');

        $this->email->setFormat('invalid');
    }

    public function testSetFormatReturnsSelf(): void
    {
        $this->assertSame(
            $this->email,
            $this->email->setFormat(Email::TEXT)
        );
    }
}
