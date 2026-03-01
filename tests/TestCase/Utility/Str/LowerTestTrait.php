<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait LowerTestTrait
{
    public function testLowerFromLowerCase(): void
    {
        $this->assertSame(
            'this is a test string',
            Str::lower('this is a test string')
        );
    }

    public function testLowerFromUpperCase(): void
    {
        $this->assertSame(
            'this is a test string',
            Str::lower('THIS IS A TEST STRING')
        );
    }
}
