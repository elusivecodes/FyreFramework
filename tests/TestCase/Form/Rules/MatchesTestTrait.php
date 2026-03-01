<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait MatchesTestTrait
{
    public function testMatches(): void
    {
        $this->validator->add('test', Rule::matches('other'));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'test',
                'other' => 'test',
            ])
        );
    }

    public function testMatchesBothEmpty(): void
    {
        $this->validator->add('test', Rule::matches('other'));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
                'other' => '',
            ])
        );
    }

    public function testMatchesDifferent(): void
    {
        $this->validator->add('test', Rule::matches('other'));

        $this->assertSame(
            [
                'test' => ['The test must have the same value as other.'],
            ],
            $this->validator->validate([
                'test' => 'test',
                'other' => 'different',
            ])
        );
    }

    public function testMatchesEmpty(): void
    {
        $this->validator->add('test', Rule::matches('other'));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
                'other' => 'test',
            ])
        );
    }

    public function testMatchesMissing(): void
    {
        $this->validator->add('test', Rule::matches('other'));

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }

    public function testMatchesOtherEmpty(): void
    {
        $this->validator->add('test', Rule::matches('other'));

        $this->assertSame(
            [
                'test' => ['The test must have the same value as other.'],
            ],
            $this->validator->validate([
                'test' => 'test',
                'other' => '',
            ])
        );
    }
}
