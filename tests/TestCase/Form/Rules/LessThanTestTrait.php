<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait LessThanTestTrait
{
    public function testLessThan(): void
    {
        $this->validator->add('test', Rule::lessThan(2));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 1,
            ])
        );
    }

    public function testLessThanAbove(): void
    {
        $this->validator->add('test', Rule::lessThan(2));

        $this->assertSame(
            [
                'test' => ['The test must be less than 2.'],
            ],
            $this->validator->validate([
                'test' => 3,
            ])
        );
    }

    public function testLessThanEmpty(): void
    {
        $this->validator->add('test', Rule::lessThan(2));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testLessThanEquals(): void
    {
        $this->validator->add('test', Rule::lessThan(2));

        $this->assertSame(
            [
                'test' => ['The test must be less than 2.'],
            ],
            $this->validator->validate([
                'test' => 2,
            ])
        );
    }

    public function testLessThanMissing(): void
    {
        $this->validator->add('test', Rule::lessThan(2));

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }
}
