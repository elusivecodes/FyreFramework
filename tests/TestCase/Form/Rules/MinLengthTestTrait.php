<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait MinLengthTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function minLengthProvider(): array
    {
        return [
            'value' => [['test' => 'test'], []],
            'empty' => [['test' => ''], []],
            'exact' => [['test' => '123'], []],
            'invalid' => [['test' => 'a'], ['test' => ['The test length must be at least 3.']]],
            'missing' => [[], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('minLengthProvider')]
    public function testMinLength(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::minLength(3));

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
