<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared;

use Fyre\DB\Pagination\CursorPage;
use Fyre\DB\Pagination\Page;
use Fyre\DB\Pagination\PageWithTotal;
use Fyre\Event\Event;
use Fyre\ORM\Queries\SelectQuery;
use Tests\Mock\Entities\Item;

use function array_column;

trait PaginationTestTrait
{
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

    public function testPaginateByCursorBeforeFindOrder(): void
    {
        $count = 0;
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

        $Items->getEventManager()->on(
            'ORM.beforeFind',
            static function(Event $event, SelectQuery $query) use (&$count): void {
                $count++;
                $query->orderBy([
                    'Items.id' => 'DESC',
                ], true);
            }
        );

        $query = $Items->find();
        $firstPage = $query->paginateByCursor(null, 2);

        $this->assertSame(
            [3, 2],
            array_column($firstPage->items(), 'id')
        );
        $this->assertSame(1, $count);

        $this->assertSame(
            [1],
            array_column(
                $query->paginateByCursor($firstPage->nextCursor(), 2)->items(),
                'id'
            )
        );
        $this->assertSame(2, $count);
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

    public function testPaginateByCursorSelectedAlias(): void
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
            ->select([
                'sort_id' => 'id',
            ])
            ->orderBy([
                'sort_id' => 'ASC',
            ]);

        $firstPage = $query->paginateByCursor(null, 2);

        $this->assertSame(
            [1, 2],
            array_column($firstPage->items(), 'id')
        );

        $this->assertSame(
            [3],
            array_column(
                $query->paginateByCursor($firstPage->nextCursor(), 2)->items(),
                'id'
            )
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
}
