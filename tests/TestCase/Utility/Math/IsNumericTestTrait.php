<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Math;

use Fyre\Utility\Math;
use PHPUnit\Framework\Attributes\DataProvider;

trait IsNumericTestTrait
{
    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function isNumericProvider(): array
    {
        return [
            'integer string' => ['100', true],
            'array' => [[], false],
            'boolean' => [false, false],
            'decimal string' => ['1.5', true],
            'float' => [1.5, true],
            'integer' => [100, true],
            'null' => [null, false],
        ];
    }

    #[DataProvider('isNumericProvider')]
    public function testIsNumeric(mixed $value, bool $expected): void
    {
        $this->assertSame(
            $expected,
            Math::isNumeric($value)
        );
    }
}
