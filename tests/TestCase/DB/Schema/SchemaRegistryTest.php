<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Schema\Column;
use Fyre\DB\Schema\ForeignKey;
use Fyre\DB\Schema\Index;
use Fyre\DB\Schema\Schema;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\Schema\Table;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class SchemaRegistryTest extends TestCase
{
    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(SchemaRegistry::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Schema::class)
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
            class_uses(Schema::class)
        );
    }
}
