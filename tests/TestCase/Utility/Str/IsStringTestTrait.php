<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;
use PHPUnit\Framework\Attributes\DataProvider;

trait IsStringTestTrait
{
    /**
     * @return array<string, array{array<int, int>|bool|float|int|string|null, bool}>
     */
    public static function isStringProvider(): array
    {
        return [
            'value' => ['This is a test string', true],
            'array' => [[1, 2, 3], false],
            'boolean' => [true, false],
            'float' => [123.456, false],
            'int' => [123, false],
            'null' => [null, false],
        ];
    }

    #[DataProvider('isStringProvider')]
    public function testIsString(mixed $value, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Str::isString($value)
        );
    }
}
