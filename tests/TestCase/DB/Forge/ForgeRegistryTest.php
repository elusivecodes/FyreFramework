<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Forge\Column;
use Fyre\DB\Forge\ForeignKey;
use Fyre\DB\Forge\Forge;
use Fyre\DB\Forge\ForgeRegistry;
use Fyre\DB\Forge\Index;
use Fyre\DB\Forge\Table;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class ForgeRegistryTest extends TestCase
{
    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(ForgeRegistry::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Forge::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Table::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Column::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Index::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(ForeignKey::class)
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Forge::class)
        );
    }
}
