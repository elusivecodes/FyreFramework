<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Time;

trait TimeTestTrait
{
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

    public function testTimeEmpty(): void
    {
        $this->validator->add('test', Rule::time());

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testTimeInvalid(): void
    {
        $this->validator->add('test', Rule::time());

        $this->assertArraysAreIdentical(
            [
                'test' => ['invalid'],
            ],
            $this->validator->validate([
                'test' => 'invalid',
            ])
        );
    }

    public function testTimeMissing(): void
    {
        $this->validator->add('test', Rule::time());

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([])
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

    public function testTimeString(): void
    {
        $this->validator->add('test', Rule::time());

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([
                'test' => '00:00:00',
            ])
        );
    }
}
