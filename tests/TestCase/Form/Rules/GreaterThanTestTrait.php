<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait GreaterThanTestTrait
{
    public function testGreaterThan(): void
    {
        $this->validator->add('test', Rule::greaterThan(2));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 3,
            ])
        );
    }

    public function testGreaterThanBelow(): void
    {
        $this->validator->add('test', Rule::greaterThan(2));

        $this->assertSame(
            [
                'test' => ['The test must be greater than 2.'],
            ],
            $this->validator->validate([
                'test' => 1,
            ])
        );
    }

    public function testGreaterThanEmpty(): void
    {
        $this->validator->add('test', Rule::greaterThan(2));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testGreaterThanEquals(): void
    {
        $this->validator->add('test', Rule::greaterThan(2));

        $this->assertSame(
            [
                'test' => ['The test must be greater than 2.'],
            ],
            $this->validator->validate([
                'test' => 2,
            ])
        );
    }

    public function testGreaterThanMissing(): void
    {
        $this->validator->add('test', Rule::greaterThan(2));

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }
}
