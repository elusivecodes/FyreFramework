<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait NaturalNumberTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function naturalNumberProvider(): array
    {
        return [
            'value' => [['test' => '123'], []],
            'decimal' => [['test' => '123.456'], ['test' => ['The test must be a natural number.']]],
            'empty' => [['test' => ''], []],
            'invalid' => [['test' => 'invalid'], ['test' => ['The test must be a natural number.']]],
            'missing' => [[], []],
            'negative' => [['test' => '-123'], ['test' => ['The test must be a natural number.']]],
            'zero' => [['test' => '0'], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('naturalNumberProvider')]
    public function testNaturalNumber(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::naturalNumber());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
