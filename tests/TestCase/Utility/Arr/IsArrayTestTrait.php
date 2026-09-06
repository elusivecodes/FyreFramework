<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Arr;

use Fyre\Utility\Arr;
use PHPUnit\Framework\Attributes\DataProvider;

trait IsArrayTestTrait
{
    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function isArrayProvider(): array
    {
        return [
            'array' => [[1, 2, 3], true],
            'boolean' => [true, false],
            'float' => [123.456, false],
            'integer' => [123, false],
            'null' => [null, false],
            'string' => ['This is a test string', false],
        ];
    }

    #[DataProvider('isArrayProvider')]
    public function testIsArray(mixed $value, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Arr::isArray($value)
        );
    }
}
