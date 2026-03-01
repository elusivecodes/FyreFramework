<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\ORM\Model;
use Fyre\ORM\ModelRegistry;
use Fyre\ORM\Relationship;
use Fyre\ORM\Result;
use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\Mysql\MysqlConnectionTrait;

use function class_uses;

final class ModelRegistryTest extends TestCase
{
    use MysqlConnectionTrait;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(ModelRegistry::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Model::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Relationship::class)
        );

        $this->assertContains(
            DebugTrait::class,
            class_uses(Result::class)
        );
    }

    public function testGetNamespaces(): void
    {
        $this->assertSame(
            [
                'Tests\Mock\Models\ORM\\',
            ],
            $this->modelRegistry->getNamespaces()
        );
    }

    public function testHasNamespace(): void
    {
        $this->assertTrue(
            $this->modelRegistry->hasNamespace('Tests\Mock\Models\ORM')
        );
    }

    public function testHasNamespaceInvalid(): void
    {
        $this->assertFalse(
            $this->modelRegistry->hasNamespace('Tests\Invalid\Model')
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Model::class)
        );
    }

    public function testRemoveNamespace(): void
    {
        $this->assertSame(
            $this->modelRegistry,
            $this->modelRegistry->removeNamespace('Tests\Mock\Models\ORM')
        );

        $this->assertFalse(
            $this->modelRegistry->hasNamespace('Tests\Mock\Models\ORM')
        );
    }

    public function testRemoveNamespaceInvalid(): void
    {
        $this->assertSame(
            $this->modelRegistry,
            $this->modelRegistry->removeNamespace('Tests\Invalid\Model')
        );
    }
}
