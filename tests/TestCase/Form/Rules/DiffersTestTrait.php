<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait DiffersTestTrait
{
    public function testDiffers(): void
    {
        $this->validator->add('test', Rule::differs('other'));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'test',
                'other' => 'different',
            ])
        );
    }

    public function testDiffersBothEmpty(): void
    {
        $this->validator->add('test', Rule::differs('other'));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
                'other' => '',
            ])
        );
    }

    public function testDiffersEmpty(): void
    {
        $this->validator->add('test', Rule::differs('other'));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
                'other' => 'test',
            ])
        );
    }

    public function testDiffersMissing(): void
    {
        $this->validator->add('test', Rule::differs('other'));

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }

    public function testDiffersOtherEmpty(): void
    {
        $this->validator->add('test', Rule::differs('other'));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'test',
                'other' => '',
            ])
        );
    }

    public function testDiffersSame(): void
    {
        $this->validator->add('test', Rule::differs('other'));

        $this->assertSame(
            [
                'test' => ['The test must have a different value than other.'],
            ],
            $this->validator->validate([
                'test' => 'test',
                'other' => 'test',
            ])
        );
    }
}
