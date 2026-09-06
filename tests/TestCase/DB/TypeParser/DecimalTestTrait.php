<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

use PHPUnit\Framework\Attributes\DataProvider;

trait DecimalTestTrait
{
    /**
     * @return array<string, array{string|null, string|null}>
     */
    public static function decimalParseProvider(): array
    {
        return [
            'default' => ['33.3', '33.3'],
            'invalid' => [null, 'invalid'],
            'null' => [null, null],
        ];
    }

    /**
     * @return array<string, array{string|null, string|null}>
     */
    public static function decimalToDatabaseProvider(): array
    {
        return [
            'default' => ['33.3', '33.3'],
            'invalid' => [null, 'invalid'],
            'null' => [null, null],
        ];
    }

    public function testDecimalFromDatabase(): void
    {
        $this->assertSame(
            '33.3',
            $this->type->use('decimal')->fromDatabase('33.3')
        );
    }

    public function testDecimalFromDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('decimal')->fromDatabase(null)
        );
    }

    #[DataProvider('decimalParseProvider')]
    public function testDecimalParse(string|null $expected, string|null $value): void
    {
        $this->assertSame(
            $expected,
            $this->type->use('decimal')->parse($value)
        );
    }

    #[DataProvider('decimalToDatabaseProvider')]
    public function testDecimalToDatabase(string|null $expected, string|null $value): void
    {
        $this->assertSame(
            $expected,
            $this->type->use('decimal')->toDatabase($value)
        );
    }
}
