<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait RequiredTestTrait
{
    public function testRequired(): void
    {
        $this->validator->add('test', Rule::required());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'test',
            ])
        );
    }

    public function testRequiredEmpty(): void
    {
        $this->validator->add('test', Rule::required());

        $this->assertSame(
            [
                'test' => ['The test is required.'],
            ],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testRequiredFalsey(): void
    {
        $this->validator->add('test', Rule::required());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '0',
            ])
        );
    }

    public function testRequiredMissing(): void
    {
        $this->validator->add('test', Rule::required());

        $this->assertSame(
            [
                'test' => ['The test is required.'],
            ],
            $this->validator->validate([])
        );
    }
}
