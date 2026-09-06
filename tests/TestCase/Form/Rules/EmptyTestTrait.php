<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait EmptyTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function emptyProvider(): array
    {
        return [
            'value' => [['test' => 'test'], ['test' => ['invalid']]],
            'empty' => [['test' => ''], []],
            'falsey' => [['test' => '0'], ['test' => ['invalid']]],
            'missing' => [[], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('emptyProvider')]
    public function testEmpty(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::empty());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
