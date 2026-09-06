<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait BooleanTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function booleanProvider(): array
    {
        return [
            'value' => [['test' => '1'], []],
            'empty' => [['test' => ''], []],
            'false' => [['test' => 'false'], []],
            'invalid' => [['test' => 'invalid'], ['test' => ['The test must be a boolean value.']]],
            'missing' => [[], []],
            'true' => [['test' => 'true'], []],
            'zero' => [['test' => '0'], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('booleanProvider')]
    public function testBoolean(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::boolean());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
