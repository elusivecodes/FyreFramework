<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use Fyre\Utility\DateTime\DateTime;
use PHPUnit\Framework\Attributes\DataProvider;

trait DateTimeTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function dateTimeValuesProvider(): array
    {
        return [
            'empty' => [['test' => ''], []],
            'invalid' => [['test' => 'invalid'], ['test' => ['invalid']]],
            'missing' => [[], []],
            'string' => [['test' => '2022-01-01 00:00:00'], []],
        ];
    }

    public function testDateTime(): void
    {
        $this->validator->add('test', Rule::dateTime());

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([
                'test' => DateTime::now(),
            ])
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('dateTimeValuesProvider')]
    public function testDateTimeValues(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::dateTime());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
