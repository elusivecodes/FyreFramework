<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait NaturalNumberTestTrait
{
    public function testNaturalNumber(): void
    {
        $this->validator->add('test', Rule::naturalNumber());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '123',
            ])
        );
    }

    public function testNaturalNumberDecimal(): void
    {
        $this->validator->add('test', Rule::naturalNumber());

        $this->assertSame(
            [
                'test' => ['The test must be a natural number.'],
            ],
            $this->validator->validate([
                'test' => '123.456',
            ])
        );
    }

    public function testNaturalNumberEmpty(): void
    {
        $this->validator->add('test', Rule::naturalNumber());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testNaturalNumberInvalid(): void
    {
        $this->validator->add('test', Rule::naturalNumber());

        $this->assertSame(
            [
                'test' => ['The test must be a natural number.'],
            ],
            $this->validator->validate([
                'test' => 'invalid',
            ])
        );
    }

    public function testNaturalNumberMissing(): void
    {
        $this->validator->add('test', Rule::naturalNumber());

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }

    public function testNaturalNumberNegative(): void
    {
        $this->validator->add('test', Rule::naturalNumber());

        $this->assertSame(
            [
                'test' => ['The test must be a natural number.'],
            ],
            $this->validator->validate([
                'test' => '-123',
            ])
        );
    }

    public function testNaturalNumberZero(): void
    {
        $this->validator->add('test', Rule::naturalNumber());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '0',
            ])
        );
    }
}
