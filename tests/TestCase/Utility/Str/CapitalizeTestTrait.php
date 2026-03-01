<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait CapitalizeTestTrait
{
    public function testCapitalizeFromLowerCase(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::capitalize('this is a test string')
        );
    }

    public function testCapitalizeFromUppercase(): void
    {
        $this->assertSame(
            'This is a test string',
            Str::capitalize('THIS IS A TEST STRING')
        );
    }
}
