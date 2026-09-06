<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait MatchesTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function matchesProvider(): array
    {
        return [
            'value' => [['test' => 'test', 'other' => 'test'], []],
            'both empty' => [['test' => '', 'other' => ''], []],
            'different' => [['test' => 'test', 'other' => 'different'], ['test' => ['The test must have the same value as other.']]],
            'empty' => [['test' => '', 'other' => 'test'], []],
            'missing' => [[], []],
            'other empty' => [['test' => 'test', 'other' => ''], ['test' => ['The test must have the same value as other.']]],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('matchesProvider')]
    public function testMatches(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::matches('other'));

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
