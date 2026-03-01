<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait BetweenTestTrait
{
    public function testBetween(): void
    {
        $this->validator->add('test', Rule::between(5, 10));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '7',
            ])
        );
    }

    public function testBetweenAbove(): void
    {
        $this->validator->add('test', Rule::between(5, 10));

        $this->assertSame(
            [
                'test' => ['The test must be between 5 and 10.'],
            ],
            $this->validator->validate([
                'test' => '12',
            ])
        );
    }

    public function testBetweenBelow(): void
    {
        $this->validator->add('test', Rule::between(5, 10));

        $this->assertSame(
            [
                'test' => ['The test must be between 5 and 10.'],
            ],
            $this->validator->validate([
                'test' => '1',
            ])
        );
    }

    public function testBetweenEmpty(): void
    {
        $this->validator->add('test', Rule::between(5, 10));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testBetweenLower(): void
    {
        $this->validator->add('test', Rule::between(5, 10));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '5',
            ])
        );
    }

    public function testBetweenMissing(): void
    {
        $this->validator->add('test', Rule::between(5, 10));

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }

    public function testBetweenUpper(): void
    {
        $this->validator->add('test', Rule::between(5, 10));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '10',
            ])
        );
    }
}
