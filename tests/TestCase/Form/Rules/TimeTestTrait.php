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
     * @return array<string, array{class-string<Date|DateTime|Time>, array<string, list<string>>}>
     */
    public static function timeObjectsProvider(): array
    {
        return [
            'time' => [
                Time::class,
                [],
            ],
            'date' => [
                Date::class,
                ['test' => ['invalid']],
            ],
            'date time' => [
                DateTime::class,
                ['test' => ['invalid']],
            ],
        ];
    }

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

    /**
     * @param class-string<Date|DateTime|Time> $class
     * @param array<string, list<string>> $expected
     */
    #[DataProvider('timeObjectsProvider')]
    public function testTimeObjects(string $class, array $expected): void
    {
        $this->validator->add('test', Rule::time());

        $this->assertArraysAreIdentical(
            $expected,
            $this->validator->validate([
                'test' => $class::now(),
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
