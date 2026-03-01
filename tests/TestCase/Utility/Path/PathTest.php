<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\Path;

use Fyre\Core\Traits\StaticMacroTrait;
use Fyre\Utility\Path;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class PathTest extends TestCase
{
    use BaseNameTestTrait;
    use DirNameTestTrait;
    use ExtensionTestTrait;
    use FileNameTestTrait;
    use FormatTestTrait;
    use IsAbsoluteTestTrait;
    use JoinTestTrait;
    use NormalizeTestTrait;
    use ParseTestTrait;
    use ResolveTestTrait;

    public function testMacro(): void
    {
        $this->assertContains(
            StaticMacroTrait::class,
            class_uses(Path::class)
        );
    }
}
