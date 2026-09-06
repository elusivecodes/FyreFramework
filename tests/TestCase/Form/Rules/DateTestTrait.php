<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Time;
use PHPUnit\Framework\Attributes\DataProvider;

trait DateTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function dateValuesProvider(): array
    {
        return [
            'empty' => [['test' => ''], []],
            'invalid' => [['test' => 'invalid'], ['test' => ['invalid']]],
            'missing' => [[], []],
            'string' => [['test' => '2022-01-01'], []],
        ];
    }

    public function testDate(): void
    {
        $this->validator->add('test', Rule::date());

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([
                'test' => Date::now(),
            ])
        );
    }

    public function testDateRejectsDateTime(): void
    {
        $this->validator->add('test', Rule::date());

        $this->assertArraysAreIdentical(
            [
                'test' => ['invalid'],
            ],
            $this->validator->validate([
                'test' => DateTime::now(),
            ])
        );
    }

    public function testDateRejectsTime(): void
    {
        $this->validator->add('test', Rule::date());

        $this->assertArraysAreIdentical(
            [
                'test' => ['invalid'],
            ],
            $this->validator->validate([
                'test' => Time::now(),
            ])
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('dateValuesProvider')]
    public function testDateValues(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::date());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
