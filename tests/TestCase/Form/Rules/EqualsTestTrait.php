<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait EqualsTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function equalsValuesProvider(): array
    {
        return [
            'above' => [['test' => '3'], ['test' => ['The test must be equal to 2.']]],
            'below' => [['test' => '1'], ['test' => ['The test must be equal to 2.']]],
            'empty' => [['test' => ''], []],
            'equals' => [['test' => '2'], []],
            'invalid' => [['test' => 'invalid'], ['test' => ['The test must be equal to 2.']]],
            'missing' => [[], []],
        ];
    }

    public function testEquals(): void
    {
        $this->validator->add('test', Rule::equals('test'));

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([
                'test' => 'test',
            ])
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('equalsValuesProvider')]
    public function testEqualsValues(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::equals('2'));

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
