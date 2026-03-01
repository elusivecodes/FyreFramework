<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait ExactLengthTestTrait
{
    public function testExactLength(): void
    {
        $this->validator->add('test', Rule::exactLength(3));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '123',
            ])
        );
    }

    public function testExactLengthEmpty(): void
    {
        $this->validator->add('test', Rule::exactLength(3));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testExactLengthInvalid(): void
    {
        $this->validator->add('test', Rule::exactLength(3));

        $this->assertSame(
            [
                'test' => ['The test length must be exactly 3.'],
            ],
            $this->validator->validate([
                'test' => 'invalid',
            ])
        );
    }

    public function testExactLengthMissing(): void
    {
        $this->validator->add('test', Rule::exactLength(3));

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }
}
