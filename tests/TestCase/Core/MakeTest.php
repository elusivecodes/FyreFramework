<?php
declare(strict_types=1);

namespace Tests\TestCase\Core;

use Fyre\Core\Make;
use Fyre\Core\Traits\DebugTrait;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class MakeTest extends TestCase
{
    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Make::class)
        );
    }
}
