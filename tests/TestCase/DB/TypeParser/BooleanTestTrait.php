<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

use PHPUnit\Framework\Attributes\DataProvider;

trait BooleanTestTrait
{
    /**
     * @return array<string, array{bool|string|null, bool|null}>
     */
    public static function booleanFromDatabaseProvider(): array
    {
        return [
            'one' => ['1', true],
            'false' => [false, false],
            'null' => [null, null],
            'true' => [true, true],
            'zero' => ['0', false],
        ];
    }

    /**
     * @return array<string, array{bool|string|null, bool|null}>
     */
    public static function booleanParseProvider(): array
    {
        return [
            'one' => ['1', true],
            'false' => [false, false],
            'invalid' => ['invalid', null],
            'null' => [null, null],
            'true' => [true, true],
            'zero' => ['0', false],
        ];
    }

    /**
     * @return array<string, array{bool|string|null, bool|null}>
     */
    public static function booleanToDatabaseProvider(): array
    {
        return [
            'one' => ['1', true],
            'false' => [false, false],
            'invalid' => ['invalid', null],
            'null' => [null, null],
            'true' => [true, true],
            'zero' => ['0', false],
        ];
    }

    #[DataProvider('booleanFromDatabaseProvider')]
    public function testBooleanFromDatabase(bool|string|null $value, bool|null $expected): void
    {
        $this->assertSame(
            $expected,
            $this->type->use('boolean')->fromDatabase($value)
        );
    }

    #[DataProvider('booleanParseProvider')]
    public function testBooleanParse(bool|string|null $value, bool|null $expected): void
    {
        $this->assertSame(
            $expected,
            $this->type->use('boolean')->parse($value)
        );
    }

    #[DataProvider('booleanToDatabaseProvider')]
    public function testBooleanToDatabase(bool|string|null $value, bool|null $expected): void
    {
        $this->assertSame(
            $expected,
            $this->type->use('boolean')->toDatabase($value)
        );
    }
}
