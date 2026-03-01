<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait AsciiTestTrait
{
    public function testAscii(): void
    {
        $this->validator->add('test', Rule::ascii());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'test123!',
            ])
        );
    }

    public function testAsciiEmpty(): void
    {
        $this->validator->add('test', Rule::ascii());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testAsciiInvalid(): void
    {
        $this->validator->add('test', Rule::ascii());

        $this->assertSame(
            [
                'test' => ['The test must only contain ASCII characters.'],
            ],
            $this->validator->validate([
                'test' => 'invalid♫',
            ])
        );
    }

    public function testAsciiMissing(): void
    {
        $this->validator->add('test', Rule::ascii());

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }
}
