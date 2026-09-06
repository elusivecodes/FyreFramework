<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait EmailTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function emailProvider(): array
    {
        return [
            'value' => [['test' => 'test@test.com'], []],
            'empty' => [['test' => ''], []],
            'invalid' => [['test' => 'invalid'], ['test' => ['The test must be a valid email address.']]],
            'missing' => [[], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('emailProvider')]
    public function testEmail(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::email());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
