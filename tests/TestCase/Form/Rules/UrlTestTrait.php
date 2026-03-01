<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait UrlTestTrait
{
    public function testUrl(): void
    {
        $this->validator->add('test', Rule::url());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'https://test.com/',
            ])
        );
    }

    public function testUrlEmpty(): void
    {
        $this->validator->add('test', Rule::url());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testUrlInvalid(): void
    {
        $this->validator->add('test', Rule::url());

        $this->assertSame(
            [
                'test' => ['The test must be a valid URL.'],
            ],
            $this->validator->validate([
                'test' => 'invalid',
            ])
        );
    }

    public function testUrlMissing(): void
    {
        $this->validator->add('test', Rule::url());

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }
}
