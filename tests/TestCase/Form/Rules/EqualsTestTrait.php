<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait EqualsTestTrait
{
    public function testEquals(): void
    {
        $this->validator->add('test', Rule::equals('test'));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'test',
            ])
        );
    }

    public function testEqualsAbove(): void
    {
        $this->validator->add('test', Rule::equals('2'));

        $this->assertSame(
            [
                'test' => ['The test must be equal to 2.'],
            ],
            $this->validator->validate([
                'test' => '3',
            ])
        );
    }

    public function testEqualsBelow(): void
    {
        $this->validator->add('test', Rule::equals('2'));

        $this->assertSame(
            [
                'test' => ['The test must be equal to 2.'],
            ],
            $this->validator->validate([
                'test' => '1',
            ])
        );
    }

    public function testEqualsEmpty(): void
    {
        $this->validator->add('test', Rule::equals('2'));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testEqualsEquals(): void
    {
        $this->validator->add('test', Rule::equals('2'));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '2',
            ])
        );
    }

    public function testEqualsInvalid(): void
    {
        $this->validator->add('test', Rule::equals('2'));

        $this->assertSame(
            [
                'test' => ['The test must be equal to 2.'],
            ],
            $this->validator->validate([
                'test' => 'invalid',
            ])
        );
    }

    public function testEqualsMissing(): void
    {
        $this->validator->add('test', Rule::equals('2'));

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }
}
