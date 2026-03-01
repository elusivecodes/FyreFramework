<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait AlphaTestTrait
{
    public function testAlpha(): void
    {
        $this->validator->add('test', Rule::alpha());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'test',
            ])
        );
    }

    public function testAlphaEmpty(): void
    {
        $this->validator->add('test', Rule::alpha());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testAlphaInvalid(): void
    {
        $this->validator->add('test', Rule::alpha());

        $this->assertSame(
            [
                'test' => ['The test must only contain alphabetical characters.'],
            ],
            $this->validator->validate([
                'test' => 'invalid123',
            ])
        );
    }

    public function testAlphaMissing(): void
    {
        $this->validator->add('test', Rule::alpha());

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }
}
