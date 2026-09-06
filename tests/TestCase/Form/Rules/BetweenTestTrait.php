<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait BetweenTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function betweenProvider(): array
    {
        return [
            'value' => [['test' => '7'], []],
            'above' => [['test' => '12'], ['test' => ['The test must be between 5 and 10.']]],
            'below' => [['test' => '1'], ['test' => ['The test must be between 5 and 10.']]],
            'empty' => [['test' => ''], []],
            'lower' => [['test' => '5'], []],
            'missing' => [[], []],
            'upper' => [['test' => '10'], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('betweenProvider')]
    public function testBetween(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::between(5, 10));

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
