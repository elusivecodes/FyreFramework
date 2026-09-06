<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait IntegerTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function integerProvider(): array
    {
        return [
            'value' => [['test' => '123'], []],
            'decimal' => [['test' => '123.456'], ['test' => ['The test must be an integer value.']]],
            'empty' => [['test' => ''], []],
            'invalid' => [['test' => 'invalid'], ['test' => ['The test must be an integer value.']]],
            'negative' => [['test' => '-123'], []],
            'zero' => [['test' => '0'], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('integerProvider')]
    public function testInteger(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::integer());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
