<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait GreaterThanTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function greaterThanProvider(): array
    {
        return [
            'value' => [['test' => 3], []],
            'below' => [['test' => 1], ['test' => ['The test must be greater than 2.']]],
            'empty' => [['test' => ''], []],
            'equals' => [['test' => 2], ['test' => ['The test must be greater than 2.']]],
            'missing' => [[], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('greaterThanProvider')]
    public function testGreaterThan(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::greaterThan(2));

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
