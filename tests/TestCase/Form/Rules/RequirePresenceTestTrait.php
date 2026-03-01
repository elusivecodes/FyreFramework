<?php
declare(strict_types=1);

namespace Tests\TestCase\Form\Rules;

use Fyre\Form\Rule;

trait RequirePresenceTestTrait
{
    public function testRequirePresence(): void
    {
        $this->validator->add('test', Rule::requirePresence());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => 'test',
            ])
        );
    }

    public function testRequirePresenceEmpty(): void
    {
        $this->validator->add('test', Rule::requirePresence());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '',
            ])
        );
    }

    public function testRequirePresenceFalsey(): void
    {
        $this->validator->add('test', Rule::requirePresence());

        $this->assertSame(
            [],
            $this->validator->validate([
                'test' => '0',
            ])
        );
    }

    public function testRequirePresenceMissing(): void
    {
        $this->validator->add('test', Rule::requirePresence());

        $this->assertSame(
            [
                'test' => ['The test must be set.'],
            ],
            $this->validator->validate([])
        );
    }
}
