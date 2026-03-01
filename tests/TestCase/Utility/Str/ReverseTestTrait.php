<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait ReverseTestTrait
{
    public function testReverseWithEmptyString(): void
    {
        $this->assertSame(
            '',
            Str::reverse('')
        );
    }

    public function testReverseWithString(): void
    {
        $this->assertSame(
            'gnirts tset a si sihT',
            Str::reverse('This is a test string')
        );
    }
}
