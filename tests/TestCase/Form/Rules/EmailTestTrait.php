<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait EmailTestTrait
{
    public function testEmail(): void
    {
        $this->validator->add('test', Rule::email());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'test@test.com',
            ])
        );
    }

    public function testEmailEmpty(): void
    {
        $this->validator->add('test', Rule::email());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testEmailInvalid(): void
    {
        $this->validator->add('test', Rule::email());

        $this->assertSame(
            [
                'test' => ['The test must be a valid email address.'],
            ],
            $this->validator->validate([
                'test' => 'invalid',
            ])
        );
    }

    public function testEmailMissing(): void
    {
        $this->validator->add('test', Rule::email());

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }
}
