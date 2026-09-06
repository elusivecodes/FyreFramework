<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

use PHPUnit\Framework\Attributes\DataProvider;

trait IntegerTestTrait
{
    /**
     * @return array<string, array{int|null, string|null}>
     */
    public static function integerParseProvider(): array
    {
        return [
            'default' => [33, '33'],
            'invalid' => [null, 'invalid'],
            'null' => [null, null],
        ];
    }

    /**
     * @return array<string, array{int|null, string|null}>
     */
    public static function integerToDatabaseProvider(): array
    {
        return [
            'default' => [33, '33'],
            'invalid' => [null, 'invalid'],
            'null' => [null, null],
        ];
    }

    public function testIntegerFromDatabase(): void
    {
        $this->assertSame(
            33,
            $this->type->use('integer')->fromDatabase('33')
        );
    }

    public function testIntegerFromDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('integer')->fromDatabase(null)
        );
    }

    #[DataProvider('integerParseProvider')]
    public function testIntegerParse(int|null $expected, string|null $value): void
    {
        $this->assertSame(
            $expected,
            $this->type->use('integer')->parse($value)
        );
    }

    #[DataProvider('integerToDatabaseProvider')]
    public function testIntegerToDatabase(int|null $expected, string|null $value): void
    {
        $this->assertSame(
            $expected,
            $this->type->use('integer')->toDatabase($value)
        );
    }
}
