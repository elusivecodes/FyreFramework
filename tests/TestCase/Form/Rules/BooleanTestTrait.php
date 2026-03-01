<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait BooleanTestTrait
{
    public function testBoolean(): void
    {
        $this->validator->add('test', Rule::boolean());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '1',
            ])
        );
    }

    public function testBooleanEmpty(): void
    {
        $this->validator->add('test', Rule::boolean());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testBooleanFalse(): void
    {
        $this->validator->add('test', Rule::boolean());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'false',
            ])
        );
    }

    public function testBooleanInvalid(): void
    {
        $this->validator->add('test', Rule::boolean());

        $this->assertSame(
            [
                'test' => ['The test must be a boolean value.'],
            ],
            $this->validator->validate([
                'test' => 'invalid',
            ])
        );
    }

    public function testBooleanMissing(): void
    {
        $this->validator->add('test', Rule::boolean());

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }

    public function testBooleanTrue(): void
    {
        $this->validator->add('test', Rule::boolean());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'true',
            ])
        );
    }

    public function testBooleanZero(): void
    {
        $this->validator->add('test', Rule::boolean());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '0',
            ])
        );
    }
}
