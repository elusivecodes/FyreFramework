<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait LessThanTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function lessThanProvider(): array
    {
        return [
            'value' => [['test' => 1], []],
            'above' => [['test' => 3], ['test' => ['The test must be less than 2.']]],
            'empty' => [['test' => ''], []],
            'equals' => [['test' => 2], ['test' => ['The test must be less than 2.']]],
            'missing' => [[], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('lessThanProvider')]
    public function testLessThan(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::lessThan(2));

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
