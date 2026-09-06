<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait RequiredTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function requiredProvider(): array
    {
        return [
            'value' => [['test' => 'test'], []],
            'empty' => [['test' => ''], ['test' => ['The test is required.']]],
            'falsey' => [['test' => '0'], []],
            'missing' => [[], ['test' => ['The test is required.']]],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('requiredProvider')]
    public function testRequired(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::required());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
