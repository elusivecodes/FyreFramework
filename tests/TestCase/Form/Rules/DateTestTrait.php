<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Time;

trait DateTestTrait
{
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

    public function testDateEmpty(): void
    {
        $this->validator->add('test', Rule::date());

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testDateInvalid(): void
    {
        $this->validator->add('test', Rule::date());

        $this->assertArraysAreIdentical(
            [
                'test' => ['invalid'],
            ],
            $this->validator->validate([
                'test' => 'invalid',
            ])
        );
    }

    public function testDateMissing(): void
    {
        $this->validator->add('test', Rule::date());

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([])
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

    public function testDateString(): void
    {
        $this->validator->add('test', Rule::date());

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([
                'test' => '2022-01-01',
            ])
        );
    }
}
