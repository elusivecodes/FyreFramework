<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait AsciiTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function asciiProvider(): array
    {
        return [
            'value' => [['test' => 'test123!'], []],
            'empty' => [['test' => ''], []],
            'invalid' => [['test' => 'invalid♫'], ['test' => ['The test must only contain ASCII characters.']]],
            'missing' => [[], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('asciiProvider')]
    public function testAscii(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::ascii());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
