<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait MinLengthTestTrait
{
    public function testMinLength(): void
    {
        $this->validator->add('test', Rule::minLength(3));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'test',
            ])
        );
    }

    public function testMinLengthEmpty(): void
    {
        $this->validator->add('test', Rule::minLength(3));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testMinLengthExact(): void
    {
        $this->validator->add('test', Rule::minLength(3));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '123',
            ])
        );
    }

    public function testMinLengthInvalid(): void
    {
        $this->validator->add('test', Rule::minLength(3));

        $this->assertSame(
            [
                'test' => ['The test length must be at least 3.'],
            ],
            $this->validator->validate([
                'test' => 'a',
            ])
        );
    }

    public function testMinLengthMissing(): void
    {
        $this->validator->add('test', Rule::minLength(3));

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }
}
