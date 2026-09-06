<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait ExactLengthTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function exactLengthProvider(): array
    {
        return [
            'value' => [['test' => '123'], []],
            'empty' => [['test' => ''], []],
            'invalid' => [['test' => 'invalid'], ['test' => ['The test length must be exactly 3.']]],
            'missing' => [[], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('exactLengthProvider')]
    public function testExactLength(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::exactLength(3));

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
