<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait DiffersTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function differsProvider(): array
    {
        return [
            'value' => [['test' => 'test', 'other' => 'different'], []],
            'both empty' => [['test' => '', 'other' => ''], []],
            'empty' => [['test' => '', 'other' => 'test'], []],
            'missing' => [[], []],
            'other empty' => [['test' => 'test', 'other' => ''], []],
            'same' => [['test' => 'test', 'other' => 'test'], ['test' => ['The test must have a different value than other.']]],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('differsProvider')]
    public function testDiffers(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::differs('other'));

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
