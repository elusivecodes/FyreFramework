<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Time;
use PHPUnit\Framework\Attributes\DataProvider;

trait TimeTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>, array<string, string[]>}>
     */
    public static function timeValuesProvider(): array
    {
        return [
            'empty' => [['test' => ''], []],
            'invalid' => [['test' => 'invalid'], ['test' => ['invalid']]],
            'missing' => [[], []],
            'string' => [['test' => '00:00:00'], []],
        ];
    }

    public function testTime(): void
    {
        $this->validator->add('test', Rule::time());

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([
                'test' => Time::now(),
            ])
        );
    }

    public function testTimeRejectsDate(): void
    {
        $this->validator->add('test', Rule::time());

        $this->assertArraysAreIdentical(
            [
                'test' => ['invalid'],
            ],
            $this->validator->validate([
                'test' => Date::now(),
            ])
        );
    }

    public function testTimeRejectsDateTime(): void
    {
        $this->validator->add('test', Rule::time());

        $this->assertArraysAreIdentical(
            [
                'test' => ['invalid'],
            ],
            $this->validator->validate([
                'test' => DateTime::now(),
            ])
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string[]> $expected
     */
    #[DataProvider('timeValuesProvider')]
    public function testTimeValues(array $data, array $expected): void
    {
        $this->validator->add('test', Rule::time());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate($data)
        );
    }
}
