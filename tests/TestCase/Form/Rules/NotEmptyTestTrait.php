<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait NotEmptyTestTrait
{
    public function testNotEmpty(): void
    {
        $this->validator->add('test', Rule::notEmpty());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'test',
            ])
        );
    }

    public function testNotEmptyEmpty(): void
    {
        $this->validator->add('test', Rule::notEmpty());

        $this->assertSame(
            [
                'test' => ['The test must not be empty.'],
            ],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testNotEmptyFalsey(): void
    {
        $this->validator->add('test', Rule::notEmpty());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '0',
            ])
        );
    }

    public function testNotEmptyMissing(): void
    {
        $this->validator->add('test', Rule::notEmpty());

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }
}
