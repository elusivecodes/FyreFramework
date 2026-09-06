<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait MaxLengthTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function maxLengthProvider(): array
    {
        return [
            'value' => [['test' => 'a'], []],
            'empty' => [['test' => ''], []],
            'exact' => [['test' => '123'], []],
            'invalid' => [['test' => 'test'], ['test' => ['The test length must be at most 3.']]],
            'missing' => [[], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('maxLengthProvider')]
    public function testMaxLength(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::maxLength(3));

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
