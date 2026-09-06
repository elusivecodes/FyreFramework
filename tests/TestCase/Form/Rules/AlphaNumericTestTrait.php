<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait AlphaNumericTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function alphaNumericProvider(): array
    {
        return [
            'value' => [['test' => 'test123'], []],
            'empty' => [['test' => ''], []],
            'invalid' => [['test' => 'invalid123!'], ['test' => ['The test must only contain alpha-numeric characters.']]],
            'missing' => [[], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('alphaNumericProvider')]
    public function testAlphaNumeric(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::alphaNumeric());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
