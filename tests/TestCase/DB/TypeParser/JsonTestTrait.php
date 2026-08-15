<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

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
     * @return array<string, array{mixed, mixed}>
     */
    public static function jsonParseProvider(): array
    {
        $object = new stdClass();

        return [
            'string' => ['test', 'test'],
            'array' => [[1, 2, 3], [1, 2, 3]],
            'false' => [false, false],
            'null' => [null, null],
            'number' => [33.3, 33.3],
            'object' => [$object, $object],
            'true' => [true, true],
        ];
    }

    /**
     * @return array<string, array{mixed, mixed}>
     */
    public static function jsonToDatabaseProvider(): array
    {
        $object = new stdClass();
        $object->a = 1;

        return [
            'string' => ['test', '"test"'],
            'array' => [[1, 2, 3], '[1,2,3]'],
            'false' => [false, 'false'],
            'null' => [null, null],
            'number' => [33.3, '33.3'],
            'object' => [$object, '{"a":1}'],
            'true' => [true, 'true'],
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

    #[DataProvider('jsonParseProvider')]
    public function testJsonParse(mixed $value, mixed $expected): void
    {
        $this->assertSame(
            $expected,
            $this->type->use('json')->parse($value)
        );
    }

    #[DataProvider('jsonToDatabaseProvider')]
    public function testJsonToDatabase(mixed $value, mixed $expected): void
    {
        $this->assertSame(
            $expected,
            $this->type->use('json')->toDatabase($value)
        );
    }
}
