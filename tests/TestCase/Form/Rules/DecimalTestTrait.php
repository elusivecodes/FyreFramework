<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait DecimalTestTrait
{
    public function testDecimal(): void
    {
        $this->validator->add('test', Rule::decimal());

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([
                'test' => '123',
            ])
        );
    }

    public function testDecimalDecimal(): void
    {
        $this->validator->add('test', Rule::decimal());

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([
                'test' => '123.456',
            ])
        );
    }

    public function testDecimalEmpty(): void
    {
        $this->validator->add('test', Rule::decimal());

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testDecimalInvalid(): void
    {
        $this->validator->add('test', Rule::decimal());

        $this->assertArraysAreIdentical(
            [
                'test' => ['The test must be a decimal value.'],
            ],
            $this->validator->validate([
                'test' => 'invalid',
            ])
        );
    }

    public function testDecimalMissing(): void
    {
        $this->validator->add('test', Rule::decimal());

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([])
        );
    }

    public function testDecimalNegative(): void
    {
        $this->validator->add('test', Rule::decimal());

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([
                'test' => '-123',
            ])
        );
    }

    public function testDecimalZero(): void
    {
        $this->validator->add('test', Rule::decimal());

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([
                'test' => '0',
            ])
        );
    }
}
