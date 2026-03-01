<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\MariaDb;

use Fyre\Core\Traits\MacroTrait;
use Fyre\ORM\Entity;
use Fyre\ORM\Queries\DeleteQuery;
use Fyre\ORM\Queries\InsertQuery;
use Fyre\ORM\Queries\SelectQuery;
use Fyre\ORM\Queries\UpdateQuery;
use Fyre\ORM\Queries\UpsertQuery;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Entities\Item;

use function class_uses;

final class QueryTest extends TestCase
{
    use MariaDbConnectionTrait;

    public function testBuffering(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $items = $Items->find()
            ->disableAutoFields()
            ->all();

        $items->toArray();

        $this->assertSame(
            [
                [
                    'id' => 1,
                ],
                [
                    'id' => 2,
                ],
            ],
            $items->map(static fn(Entity $item): array => $item->toArray())->toArray()
        );
    }

    public function testBufferingDisabled(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $items = $Items->find()
            ->disableAutoFields()
            ->disableBuffering()
            ->all();

        $items->toArray();

        $this->assertSame(
            [],
            $items->toArray()
        );
    }

    public function testCount(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $this->assertSame(
            2,
            $Items->find()
                ->count()
        );
    }

    public function testCountWithLimit(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $this->assertSame(
            1,
            $Items->find()
                ->limit(1)
                ->count()
        );
    }

    public function testDirty(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $query = $Items->find();

        $result1 = $query->first();

        $this->assertInstanceOf(
            Item::class,
            $result1
        );

        $this->assertSame(
            'Items',
            $result1->getSource()
        );

        $this->assertSame(
            'Test 1',
            $result1->name
        );

        $query->where([
            'name' => 'Test 2',
        ]);

        $result2 = $query->first();

        $this->assertInstanceOf(
            Item::class,
            $result2
        );

        $this->assertSame(
            'Items',
            $result2->getSource()
        );

        $this->assertSame(
            'Test 2',
            $result2->name
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(DeleteQuery::class)
        );

        $this->assertContains(
            MacroTrait::class,
            class_uses(InsertQuery::class)
        );

        $this->assertContains(
            MacroTrait::class,
            class_uses(SelectQuery::class)
        );

        $this->assertContains(
            MacroTrait::class,
            class_uses(UpdateQuery::class)
        );

        $this->assertContains(
            MacroTrait::class,
            class_uses(UpsertQuery::class)
        );
    }

    public function testQuery(): void
    {
        $this->assertInstanceOf(
            SelectQuery::class,
            $this->modelRegistry->use('Items')->find()
        );
    }
}
