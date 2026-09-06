<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait RequirePresenceTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function requirePresenceProvider(): array
    {
        return [
            'value' => [['test' => 'test'], []],
            'empty' => [['test' => ''], []],
            'falsey' => [['test' => '0'], []],
            'missing' => [[], ['test' => ['The test must be set.']]],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('requirePresenceProvider')]
    public function testRequirePresence(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::requirePresence());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
