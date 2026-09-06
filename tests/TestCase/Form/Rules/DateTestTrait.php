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
     * @return array<string, array{class-string<Date|DateTime|Time>, array<string, list<string>>}>
     */
    public static function dateObjectsProvider(): array
    {
        return [
            'date' => [
                Date::class,
                [],
            ],
            'date time' => [
                DateTime::class,
                ['test' => ['invalid']],
            ],
            'time' => [
                Time::class,
                ['test' => ['invalid']],
            ],
        ];
    }

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

    /**
     * @param class-string<Date|DateTime|Time> $class
     * @param array<string, list<string>> $expected
     */
    #[DataProvider('dateObjectsProvider')]
    public function testDateObjects(string $class, array $expected): void
    {
        $this->validator->add('test', Rule::date());

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
