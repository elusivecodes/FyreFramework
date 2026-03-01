<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait RegexTestTrait
{
    public function testRegex(): void
    {
        $this->validator->add('test', Rule::regex('/test/'));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'test',
            ])
        );
    }

    public function testRegexEmpty(): void
    {
        $this->validator->add('test', Rule::regex('/test/'));

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testRegexInvalid(): void
    {
        $this->validator->add('test', Rule::regex('/test/'));

        $this->assertSame(
            [
                'test' => ['The test must match the regular expression /test/.'],
            ],
            $this->validator->validate([
                'test' => 'invalid',
            ])
        );
    }

    public function testRegexMissing(): void
    {
        $this->validator->add('test', Rule::regex('/test/'));

        $this->assertSame(
            [],
            $this->validator->validate([])
        );
    }
}
