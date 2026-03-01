<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait AlphaNumericTestTrait
{
    public function testAlphaNumeric(): void
    {
        $this->validator->add('test', Rule::alphaNumeric());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'test123',
            ])
        );
    }

    public function testAlphaNumericEmpty(): void
    {
        $this->validator->add('test', Rule::alphaNumeric());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testAlphaNumericInvalid(): void
    {
        $this->validator->add('test', Rule::alphaNumeric());

        $this->assertSame(
            [
                'test' => ['The test must only contain alpha-numeric characters.'],
            ],
            $this->validator->validate([
                'test' => 'invalid123!',
            ])
        );
    }

    public function testAlphaNumericMissing(): void
    {
        $this->validator->add('test', Rule::alphaNumeric());

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }
}
