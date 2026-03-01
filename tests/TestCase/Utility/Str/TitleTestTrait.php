<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Str;

use Fyre\Utility\Str;

trait TitleTestTrait
{
    public function testTitleFromLowerCase(): void
    {
        $this->assertSame(
            'This Is A Test String',
            Str::title('this is a test string')
        );
    }

    public function testTitleFromUpperCase(): void
    {
        $this->assertSame(
            'This Is A Test String',
            Str::title('THIS IS A TEST STRING')
        );
    }
}
