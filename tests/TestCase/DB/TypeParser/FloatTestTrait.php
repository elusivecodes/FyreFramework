<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

use PHPUnit\Framework\Attributes\DataProvider;

trait FloatTestTrait
{
    /**
     * @return array<string, array{float|null, string|null}>
     */
    public static function floatParseProvider(): array
    {
        return [
            'default' => [33.3, '33.3'],
            'invalid' => [null, 'invalid'],
            'null' => [null, null],
        ];
    }

    /**
     * @return array<string, array{float|null, string|null}>
     */
    public static function floatToDatabaseProvider(): array
    {
        return [
            'default' => [33.3, '33.3'],
            'invalid' => [null, 'invalid'],
            'null' => [null, null],
        ];
    }

    public function testFloatFromDatabase(): void
    {
        $this->assertSame(
            33.3,
            $this->type->use('float')->fromDatabase('33.3')
        );
    }

    public function testFloatFromDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('float')->fromDatabase(null)
        );
    }

    #[DataProvider('floatParseProvider')]
    public function testFloatParse(float|null $expected, string|null $value): void
    {
        $this->assertSame(
            $expected,
            $this->type->use('float')->parse($value)
        );
    }

    #[DataProvider('floatToDatabaseProvider')]
    public function testFloatToDatabase(float|null $expected, string|null $value): void
    {
        $this->assertSame(
            $expected,
            $this->type->use('float')->toDatabase($value)
        );
    }
}
