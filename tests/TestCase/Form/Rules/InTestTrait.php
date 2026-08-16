<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait InTestTrait
{
    public function testIn(): void
    {
        $this->validator->add('test', Rule::in(['test', 'other']));

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([
                'test' => 'test',
            ])
        );
    }

    public function testInEmpty(): void
    {
        $this->validator->add('test', Rule::in(['test', 'other']));

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testInInvalid(): void
    {
        $this->validator->add('test', Rule::in(['test', 'other']));

        $this->assertArraysAreIdentical(
            [
                'test' => ['The test must be one of the values: test, other'],
            ],
            $this->validator->validate([
                'test' => 'invalid',
            ])
        );
    }

    public function testInMissing(): void
    {
        $this->validator->add('test', Rule::in(['test', 'other']));

        $this->assertArraysAreIdentical(
            [],
            $this->validator->validate([])
        );
    }
}
