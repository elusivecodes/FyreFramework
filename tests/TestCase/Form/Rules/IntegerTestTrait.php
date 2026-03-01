<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait IntegerTestTrait
{
    public function testInteger(): void
    {
        $this->validator->add('test', Rule::integer());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '123',
            ])
        );
    }

    public function testIntegerDecimal(): void
    {
        $this->validator->add('test', Rule::integer());

        $this->assertSame(
            [
                'test' => ['The test must be an integer value.'],
            ],
            $this->validator->validate([
                'test' => '123.456',
            ])
        );
    }

    public function testIntegerEmpty(): void
    {
        $this->validator->add('test', Rule::integer());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testIntegerInvalid(): void
    {
        $this->validator->add('test', Rule::integer());

        $this->assertSame(
            [
                'test' => ['The test must be an integer value.'],
            ],
            $this->validator->validate([
                'test' => 'invalid',
            ])
        );
    }

    public function testIntegerNegative(): void
    {
        $this->validator->add('test', Rule::integer());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '-123',
            ])
        );
    }

    public function testIntegerZero(): void
    {
        $this->validator->add('test', Rule::integer());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '0',
            ])
        );
    }
}
