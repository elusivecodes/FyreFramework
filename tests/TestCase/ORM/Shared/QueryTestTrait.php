<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared;

use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Pagination\CursorPage;
use Fyre\DB\Pagination\Page;
use Fyre\DB\Pagination\PageWithTotal;
use Fyre\ORM\Entity;
use Fyre\ORM\Queries\DeleteQuery;
use Fyre\ORM\Queries\InsertQuery;
use Fyre\ORM\Queries\SelectQuery;
use Fyre\ORM\Queries\UpdateBatchQuery;
use Fyre\ORM\Queries\UpdateQuery;
use Fyre\ORM\Queries\UpsertQuery;
use Tests\Mock\Entities\Item;

use function class_uses;

trait QueryTestTrait
{
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

        $this->assertArraysAreIdentical(
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

        $this->assertArraysAreIdentical(
            [],
            $items->toArray()
        );
    }

    public function testClearResult(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item1 = $Items->newEntity([
            'name' => 'Test 1',
        ]);

        $this->assertTrue(
            $Items->save($item1)
        );

        $query = $Items->find();

        $this->assertCount(
            1,
            $query->all()->toArray()
        );

        $item2 = $Items->newEntity([
            'name' => 'Test 2',
        ]);

        $this->assertTrue(
            $Items->save($item2)
        );

        $this->assertCount(
            1,
            $query->all()->toArray()
        );

        $this->assertSame(
            $query,
            $query->clearResult()
        );

        $this->assertCount(
            2,
            $query->all()->toArray()
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
            $result1->getModelAlias()
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
            $result2->getModelAlias()
        );

        $this->assertSame(
            'Test 2',
            $result2->name
        );
    }

    public function testEnableAutoFields(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $item = $Items->newEntity([
            'name' => 'Test 1',
        ]);

        $this->assertTrue(
            $Items->save($item)
        );

        $query = $Items->find()
            ->disableAutoFields();

        $result = $query->first();

        $this->assertInstanceOf(
            Item::class,
            $result
        );

        $this->assertArraysAreIdentical(
            [
                'id' => 1,
            ],
            $result->toArray()
        );

        $this->assertSame(
            $query,
            $query->enableAutoFields()
        );

        $result = $query->first();

        $this->assertInstanceOf(
            Item::class,
            $result
        );

        $this->assertArraysAreIdentical(
            [
                'id' => 1,
                'name' => 'Test 1',
            ],
            $result->toArray()
        );
    }

    public function testEnableBuffering(): void
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

        $query = $Items->find()
            ->disableAutoFields()
            ->disableBuffering();

        $this->assertSame(
            $query,
            $query->enableBuffering()
        );

        $items = $query->all();

        $this->assertArraysAreIdentical(
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

    public function testPaginate(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
            [
                'name' => 'Test 3',
            ],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $query = $Items->find()
            ->orderBy([
                'id' => 'ASC',
            ]);

        $page = $query->paginate(2, 2);
        $pageItems = $page->items();

        $this->assertInstanceOf(
            Page::class,
            $page
        );

        $this->assertCount(1, $page);
        $this->assertInstanceOf(Item::class, $pageItems[0]);
        $this->assertSame(3, $pageItems[0]->id);
        $this->assertFalse($page->hasNext());
        $this->assertTrue($page->hasPrevious());
    }

    public function testPaginateByCursor(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
            [
                'name' => 'Test 3',
            ],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $query = $Items->find()
            ->orderBy([
                'Items.id' => 'ASC',
            ]);

        $firstPage = $query->paginateByCursor(null, 2);
        $firstItems = $firstPage->items();

        $this->assertInstanceOf(
            CursorPage::class,
            $firstPage
        );

        $this->assertInstanceOf(
            Item::class,
            $firstItems[0]
        );

        $this->assertSame('Test 1', $firstItems[0]->name);

        $secondPage = $query->paginateByCursor($firstPage->nextCursor(), 2);
        $secondItems = $secondPage->items();

        $this->assertCount(1, $secondPage);
        $this->assertInstanceOf(Item::class, $secondItems[0]);
        $this->assertSame(3, $secondItems[0]->id);
    }

    public function testPaginateByCursorContain(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $users = $Users->newEntities([
            [
                'name' => 'Test 1',
                'address' => [
                    'suburb' => 'B',
                ],
            ],
            [
                'name' => 'Test 2',
                'address' => [
                    'suburb' => 'A',
                ],
            ],
            [
                'name' => 'Test 3',
                'address' => [
                    'suburb' => 'C',
                ],
            ],
        ], associated: ['Addresses']);

        $this->assertTrue(
            $Users->saveMany($users)
        );

        $query = $Users->find()
            ->contain('Addresses')
            ->orderBy([
                'Addresses.suburb' => 'ASC',
                'Users.id' => 'ASC',
            ]);

        $firstPage = $query->paginateByCursor(null, 2);
        $firstItems = $firstPage->items();

        $this->assertSame('A', $firstItems[0]->address->suburb);
        $this->assertSame('B', $firstItems[1]->address->suburb);
        $this->assertFalse($firstItems[0]->has('__fyre_cursor_0'));
        $this->assertFalse($firstItems[0]->has('__fyre_cursor_1'));

        $secondPage = $query->paginateByCursor($firstPage->nextCursor(), 2);
        $secondItems = $secondPage->items();

        $this->assertCount(1, $secondItems);
        $this->assertSame('C', $secondItems[0]->address->suburb);

        $previousPage = $query->paginateByCursor($secondPage->previousCursor(), 2);
        $previousItems = $previousPage->items();

        $this->assertSame('A', $previousItems[0]->address->suburb);
        $this->assertSame('B', $previousItems[1]->address->suburb);
    }

    public function testPaginateByCursorDisabledAutoFields(): void
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

        $query = $Items->find()
            ->disableAutoFields()
            ->orderBy([
                'Items.name' => 'ASC',
                'Items.id' => 'ASC',
            ]);

        $firstPage = $query->paginateByCursor(null, 1);

        $this->assertSame(
            [
                'id' => 1,
            ],
            $firstPage->items()[0]->toArray()
        );

        $this->assertSame(
            [
                'id' => 2,
            ],
            $query->paginateByCursor($firstPage->nextCursor(), 1)->items()[0]->toArray()
        );
    }

    public function testPaginateWithTotal(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $items = $Items->newEntities([
            [
                'name' => 'Test 1',
            ],
            [
                'name' => 'Test 2',
            ],
            [
                'name' => 'Test 3',
            ],
        ]);

        $this->assertTrue(
            $Items->saveMany($items)
        );

        $query = $Items->find()
            ->orderBy([
                'id' => 'ASC',
            ])
            ->limit(1, 1);

        $page = $query->paginateWithTotal(2, 2);
        $pageItems = $page->items();

        $this->assertInstanceOf(
            PageWithTotal::class,
            $page
        );

        $this->assertCount(
            1,
            $page
        );

        $this->assertInstanceOf(
            Item::class,
            $pageItems[0]
        );

        $this->assertSame(
            3,
            $pageItems[0]->id
        );

        $this->assertSame(
            3,
            $page->totalItems()
        );

        $this->assertSame(
            1,
            $query->getLimit()
        );

        $this->assertSame(
            1,
            $query->getOffset()
        );
    }

    public function testQuery(): void
    {
        $this->assertInstanceOf(
            SelectQuery::class,
            $this->modelRegistry->use('Items')->find()
        );
    }

    public function testUpdateBatchQuery(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $query = $Items->updateBatchQuery();

        $this->assertInstanceOf(
            UpdateBatchQuery::class,
            $query
        );

        $this->assertSame(
            $Items,
            $query->getModel()
        );
    }

    public function testUpsertQuery(): void
    {
        $Items = $this->modelRegistry->use('Items');
        $query = $Items->upsertQuery(['name']);

        $this->assertInstanceOf(
            UpsertQuery::class,
            $query
        );

        $this->assertSame(
            $Items,
            $query->getModel()
        );

        $this->assertArraysAreIdentical(
            ['name'],
            $query->getConflictKeys()
        );
    }
}
