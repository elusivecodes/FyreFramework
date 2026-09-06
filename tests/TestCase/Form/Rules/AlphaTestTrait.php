<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait AlphaTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function alphaProvider(): array
    {
        return [
            'value' => [['test' => 'test'], []],
            'empty' => [['test' => ''], []],
            'invalid' => [['test' => 'invalid123'], ['test' => ['The test must only contain alphabetical characters.']]],
            'missing' => [[], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('alphaProvider')]
    public function testAlpha(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::alpha());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
