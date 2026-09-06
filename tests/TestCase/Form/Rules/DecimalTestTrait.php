<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait DecimalTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function decimalProvider(): array
    {
        return [
            'value' => [['test' => '123'], []],
            'decimal' => [['test' => '123.456'], []],
            'empty' => [['test' => ''], []],
            'invalid' => [['test' => 'invalid'], ['test' => ['The test must be a decimal value.']]],
            'missing' => [[], []],
            'negative' => [['test' => '-123'], []],
            'zero' => [['test' => '0'], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('decimalProvider')]
    public function testDecimal(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::decimal());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
