<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use PHPUnit\Framework\Attributes\DataProvider;

trait UrlTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function urlProvider(): array
    {
        return [
            'value' => [['test' => 'https://test.com/'], []],
            'empty' => [['test' => ''], []],
            'invalid' => [['test' => 'invalid'], ['test' => ['The test must be a valid URL.']]],
            'missing' => [[], []],
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('urlProvider')]
    public function testUrl(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::url());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
