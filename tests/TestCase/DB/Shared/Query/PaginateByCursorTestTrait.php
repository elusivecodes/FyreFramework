<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Shared\Query;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Pagination\AbstractPage;
use Fyre\DB\Pagination\CursorPage;
use InvalidArgumentException;

use function array_column;
use function class_uses;
use function iterator_to_array;

trait PaginateByCursorTestTrait
{
    public function testPaginateByCursor(): void
    {
        $this->assertInstanceOf(
            CursorPage::class,
            $this->db->select()
                ->from('test')
                ->orderBy([
                    'id' => 'ASC',
                ])
                ->paginateByCursor()
        );
    }

    public function testPaginateByCursorCompositeOrder(): void
    {
        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'A',
                ],
                [
                    'name' => 'A',
                ],
                [
                    'name' => 'B',
                ],
                [
                    'name' => 'B',
                ],
            ])
            ->execute();

        $query = $this->db->select()
            ->from('test')
            ->orderBy([
                'name' => 'ASC',
                'id' => 'DESC',
            ]);

        $page = $query->paginateByCursor(null, 3);

        $this->assertSame(
            [2, 1, 4],
            array_column($page->items(), 'id')
        );

        $this->assertSame(
            [3],
            array_column(
                $query->paginateByCursor($page->nextCursor(), 3)->items(),
                'id'
            )
        );
    }

    public function testPaginateByCursorCurrentCursor(): void
    {
        $this->insert();

        $query = $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ]);

        $cursor = $query->paginateByCursor(null, 1)->nextCursor();

        $this->assertSame(
            $cursor,
            $query->paginateByCursor($cursor, 1)->currentCursor()
        );
    }

    public function testPaginateByCursorDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(AbstractPage::class)
        );
    }

    public function testPaginateByCursorDescending(): void
    {
        $this->insert();

        $query = $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'DESC',
            ]);

        $page = $query->paginateByCursor(null, 2);

        $this->assertSame(
            [3, 2],
            array_column($page->items(), 'id')
        );

        $this->assertSame(
            [1],
            array_column(
                $query->paginateByCursor($page->nextCursor(), 2)->items(),
                'id'
            )
        );
    }

    public function testPaginateByCursorDistinct(): void
    {
        $this->insert();

        $query = $this->db->select([
            'id' => 'id',
        ])
            ->distinct()
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ]);

        $page = $query->paginateByCursor(null, 2);

        $this->assertSame(
            [1, 2],
            array_column($page->items(), 'id')
        );

        $this->assertSame(
            [3],
            array_column(
                $query->paginateByCursor($page->nextCursor(), 2)->items(),
                'id'
            )
        );
    }

    public function testPaginateByCursorDistinctMissingOrderField(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Cursor pagination requires all ordered fields to be explicitly selected when using DISTINCT.');

        $this->db->select('name')
            ->distinct()
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ])
            ->paginateByCursor();
    }

    public function testPaginateByCursorDuplicateAlias(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Cursor field aliases conflict with selected fields.');

        $this->db->select([
            '__fyre_cursor_0' => 'name',
        ])
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ])
            ->paginateByCursor(null, 1)
            ->items();
    }

    public function testPaginateByCursorGroupLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Query group limits cannot be used with pagination.');

        $this->db->select()
            ->from('test')
            ->groupLimit(1, 'name')
            ->orderBy([
                'id' => 'ASC',
            ])
            ->paginateByCursor();
    }

    public function testPaginateByCursorInvalidCursor(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Cursor is not valid.');

        $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ])
            ->paginateByCursor('invalid');
    }

    public function testPaginateByCursorInvalidOrderDirection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Cursor pagination order directions must be ASC or DESC.');

        $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'INVALID',
            ])
            ->paginateByCursor();
    }

    public function testPaginateByCursorInvalidOrderField(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Cursor pagination requires simple ordered fields.');

        $this->db->select()
            ->from('test')
            ->orderBy('LOWER(name) ASC')
            ->paginateByCursor();
    }

    public function testPaginateByCursorInvalidPerPage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Items per page must be greater than zero.');

        $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ])
            ->paginateByCursor(null, 0);
    }

    public function testPaginateByCursorInvalidSelectedAlias(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Cursor pagination requires ordered aliases to resolve to fields or value expressions.');

        $subquery = $this->db->select('id')
            ->from('test')
            ->limit(1);

        $this->db->select([
            'sort_id' => $subquery,
        ])
            ->from('test')
            ->orderBy([
                'sort_id' => 'ASC',
            ])
            ->paginateByCursor();
    }

    public function testPaginateByCursorIteration(): void
    {
        $this->insert();

        $page = $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ])
            ->paginateByCursor(null, 2);

        $this->assertSame(
            [1, 2],
            array_column(iterator_to_array($page), 'id')
        );
    }

    public function testPaginateByCursorJsonSerialize(): void
    {
        $this->insert();

        $page = $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ])
            ->paginateByCursor(null, 2);

        $data = $page->jsonSerialize();

        $this->assertSame(
            [1, 2],
            array_column($data['data'], 'id')
        );
        $this->assertSame(2, $data['pagination']['perPage']);
        $this->assertIsString($data['pagination']['nextCursor']);
        $this->assertNull($data['pagination']['previousCursor']);
    }

    public function testPaginateByCursorMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(CursorPage::class)
        );
    }

    public function testPaginateByCursorMismatchedOrder(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Cursor is not valid.');

        $this->insert();

        $cursor = $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ])
            ->paginateByCursor(null, 1)
            ->nextCursor();

        $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'DESC',
            ])
            ->paginateByCursor($cursor, 1);
    }

    public function testPaginateByCursorMissingOrder(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Cursor pagination requires an ORDER BY clause.');

        $this->db->select()
            ->from('test')
            ->paginateByCursor();
    }

    public function testPaginateByCursorNavigation(): void
    {
        $this->insert();

        $query = $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ]);

        $firstPage = $query->paginateByCursor(null, 2);

        $this->assertSame(
            [1, 2],
            array_column($firstPage->items(), 'id')
        );
        $this->assertTrue($firstPage->hasNext());
        $this->assertFalse($firstPage->hasPrevious());
        $this->assertNotNull($firstPage->nextCursor());
        $this->assertNull($firstPage->previousCursor());

        $secondPage = $query->paginateByCursor($firstPage->nextCursor(), 2);

        $this->assertSame(
            [3],
            array_column($secondPage->items(), 'id')
        );
        $this->assertFalse($secondPage->hasNext());
        $this->assertTrue($secondPage->hasPrevious());
        $this->assertNull($secondPage->nextCursor());
        $this->assertNotNull($secondPage->previousCursor());

        $previousPage = $query->paginateByCursor($secondPage->previousCursor(), 2);

        $this->assertSame(
            [1, 2],
            array_column($previousPage->items(), 'id')
        );
        $this->assertTrue($previousPage->hasNext());
        $this->assertFalse($previousPage->hasPrevious());
    }

    public function testPaginateByCursorOrderString(): void
    {
        $this->insert();

        $this->assertSame(
            [1, 2],
            array_column(
                $this->db->select()
                    ->from('test')
                    ->orderBy('id ASC')
                    ->paginateByCursor(null, 2)
                    ->items(),
                'id'
            )
        );
    }

    public function testPaginateByCursorOverwritesLimitOffset(): void
    {
        $this->insert();

        $query = $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ])
            ->limit(1, 1);

        $page = $query->paginateByCursor(null, 2);

        $this->assertSame(
            [1, 2],
            array_column($page->items(), 'id')
        );
        $this->assertSame(1, $query->getLimit());
        $this->assertSame(1, $query->getOffset());
    }

    public function testPaginateByCursorPerPage(): void
    {
        $this->assertSame(
            10,
            $this->db->select()
                ->from('test')
                ->orderBy([
                    'id' => 'ASC',
                ])
                ->paginateByCursor(null, 10)
                ->perPage()
        );
    }

    public function testPaginateByCursorSelectedAlias(): void
    {
        $this->insert();

        $query = $this->db->select([
            'sort_id' => 'id',
            'name',
        ])
            ->from('test')
            ->orderBy([
                'sort_id' => 'ASC',
            ]);

        $page = $query->paginateByCursor(null, 2);

        $this->assertSame(
            [1, 2],
            array_column($page->items(), 'sort_id')
        );

        $this->assertSame(
            [3],
            array_column(
                $query->paginateByCursor($page->nextCursor(), 2)->items(),
                'sort_id'
            )
        );
    }

    public function testPaginateByCursorSelectedAliasExpression(): void
    {
        $this->insert();

        $query = $this->db->select()
            ->from('test');

        $query->select([
            'sort_name' => $query->func()->lower('name'),
            'id' => 'id',
        ])
            ->orderBy([
                'sort_name' => 'ASC',
                'id' => 'ASC',
            ]);

        $page = $query->paginateByCursor(null, 2);

        $this->assertSame(
            ['test 1', 'test 2'],
            array_column($page->items(), 'sort_name')
        );

        $this->assertSame(
            ['test 3'],
            array_column(
                $query->paginateByCursor($page->nextCursor(), 2)->items(),
                'sort_name'
            )
        );
    }

    public function testPaginateByCursorSelectedField(): void
    {
        $this->insert();

        $query = $this->db->select([
            'selected_id' => 'id',
            '__fyre_cursor_0' => 'name',
        ])
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ]);

        $page = $query->paginateByCursor(null, 2);

        $this->assertSame(
            [1, 2],
            array_column($page->items(), 'selected_id')
        );
        $this->assertSame(
            ['Test 1', 'Test 2'],
            array_column($page->items(), '__fyre_cursor_0')
        );

        $this->assertSame(
            [3],
            array_column(
                $query->paginateByCursor($page->nextCursor(), 2)->items(),
                'selected_id'
            )
        );
    }

    public function testPaginateByCursorSelectsOrderField(): void
    {
        $this->insert();

        $query = $this->db->select('name')
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ]);

        $page = $query->paginateByCursor(null, 1);

        $this->assertSame(
            [
                [
                    'name' => 'Test 1',
                ],
            ],
            $page->items()
        );

        $this->assertSame(
            [
                [
                    'name' => 'Test 2',
                ],
            ],
            $query->paginateByCursor($page->nextCursor(), 1)->items()
        );
    }

    public function testPaginateByCursorSetOperation(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Cursor pagination cannot be used with set-operation queries.');

        $this->db->select()
            ->from('test')
            ->union(
                $this->db->select()
                    ->from('test')
            )
            ->orderBy([
                'id' => 'ASC',
            ])
            ->paginateByCursor();
    }
}
