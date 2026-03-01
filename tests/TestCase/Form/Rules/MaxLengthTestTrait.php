<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait MaxLengthTestTrait
{
    public function testMaxLength(): void
    {
        $this->validator->add('test', Rule::maxLength(3));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'a',
            ])
        );
    }

    public function testMaxLengthEmpty(): void
    {
        $this->validator->add('test', Rule::maxLength(3));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testMaxLengthExact(): void
    {
        $this->validator->add('test', Rule::maxLength(3));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '123',
            ])
        );
    }

    public function testMaxLengthInvalid(): void
    {
        $this->validator->add('test', Rule::maxLength(3));

        $this->assertSame(
            [
                'test' => ['The test length must be at most 3.'],
            ],
            $this->validator->validate([
                'test' => 'test',
            ])
        );
    }

    public function testMaxLengthMissing(): void
    {
        $this->validator->add('test', Rule::maxLength(3));

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }
}
