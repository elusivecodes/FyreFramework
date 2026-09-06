<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

use Closure;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

trait JsonTestTrait
{
    /**
     * @return array<string, array{mixed, mixed}>
     */
    public static function jsonFromDatabaseProvider(): array
    {
        return [
            'string' => ['"test"', 'test'],
            'array' => ['[1,2,3]', [1, 2, 3]],
            'false' => ['false', false],
            'null' => [null, null],
            'number' => ['33.3', 33.3],
            'object' => ['{"a":1}', ['a' => 1]],
            'true' => ['true', true],
        ];
    }

    /**
     * @return array<string, array{Closure(): mixed}>
     */
    public static function jsonParseProvider(): array
    {
        return [
            'string' => [static fn(): string => 'test'],
            'array' => [static fn(): array => [1, 2, 3]],
            'false' => [static fn(): bool => false],
            'null' => [static fn(): null => null],
            'number' => [static fn(): float => 33.3],
            'object' => [static fn(): stdClass => new stdClass()],
            'true' => [static fn(): bool => true],
        ];
    }

    /**
     * @return array<string, array{Closure(): mixed, string|null}>
     */
    public static function jsonToDatabaseProvider(): array
    {
        return [
            'string' => [static fn(): string => 'test', '"test"'],
            'array' => [static fn(): array => [1, 2, 3], '[1,2,3]'],
            'false' => [static fn(): bool => false, 'false'],
            'null' => [static fn(): null => null, null],
            'number' => [static fn(): float => 33.3, '33.3'],
            'object' => [static fn(): stdClass => (object) ['a' => 1], '{"a":1}'],
            'true' => [static fn(): bool => true, 'true'],
        ];
    }

    #[DataProvider('jsonFromDatabaseProvider')]
    public function testJsonFromDatabase(mixed $value, mixed $expected): void
    {
        $this->assertSame(
            $expected,
            $this->type->use('json')->fromDatabase($value)
        );
    }

    /**
     * @param Closure(): mixed $value
     */
    #[DataProvider('jsonParseProvider')]
    public function testJsonParse(Closure $value): void
    {
        $input = $value();

        $this->assertSame(
            $input,
            $this->type->use('json')->parse($input)
        );
    }

    /**
     * @param Closure(): mixed $value
     */
    #[DataProvider('jsonToDatabaseProvider')]
    public function testJsonToDatabase(Closure $value, string|null $expected): void
    {
        $input = $value();

        $this->assertSame(
            $expected,
            $this->type->use('json')->toDatabase($input)
        );
    }
}
