<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait LessThanOrEqualsTestTrait
{
    public function testLessThanOrEquals(): void
    {
        $this->validator->add('test', Rule::lessThanOrEquals(2));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 1,
            ])
        );
    }

    public function testLessThanOrEqualsAbove(): void
    {
        $this->validator->add('test', Rule::lessThanOrEquals(2));

        $this->assertSame(
            [
                'test' => ['The test must be less than or equal to 2.'],
            ],
            $this->validator->validate([
                'test' => 3,
            ])
        );
    }

    public function testLessThanOrEqualsEmpty(): void
    {
        $this->validator->add('test', Rule::lessThanOrEquals(2));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testLessThanOrEqualsEquals(): void
    {
        $this->validator->add('test', Rule::lessThanOrEquals(2));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 2,
            ])
        );
    }

    public function testLessThanOrEqualsMissing(): void
    {
        $this->validator->add('test', Rule::lessThanOrEquals(2));

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }
}
