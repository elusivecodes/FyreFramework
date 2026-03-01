<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;
use Fyre\Utility\DateTime\DateTime;

trait TimeTestTrait
{
    public function testTime(): void
    {
        $this->validator->add('test', Rule::time());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => DateTime::now(),
            ])
        );
    }

    public function testTimeEmpty(): void
    {
        $this->validator->add('test', Rule::time());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testTimeInvalid(): void
    {
        $this->validator->add('test', Rule::time());

        $this->assertSame(
            [
                'test' => ['invalid'],
            ],
            $this->validator->validate([
                'test' => 'invalid',
            ])
        );
    }

    public function testTimeMissing(): void
    {
        $this->validator->add('test', Rule::time());

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }

    public function testTimeString(): void
    {
        $this->validator->add('test', Rule::time());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '00:00:00',
            ])
        );
    }
}
