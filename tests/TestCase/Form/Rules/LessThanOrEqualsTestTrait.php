<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait LessThanOrEqualsTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function lessThanOrEqualsProvider(): array
    {
        return [
            'value' => [['test' => 1], []],
            'above' => [['test' => 3], ['test' => ['The test must be less than or equal to 2.']]],
            'empty' => [['test' => ''], []],
            'equals' => [['test' => 2], []],
            'missing' => [[], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('lessThanOrEqualsProvider')]
    public function testLessThanOrEquals(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::lessThanOrEquals(2));

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
