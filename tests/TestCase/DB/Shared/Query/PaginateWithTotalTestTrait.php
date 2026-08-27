<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Shared\Query;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Pagination\AbstractPage;
use Fyre\DB\Pagination\PageWithTotal;
use InvalidArgumentException;

use function class_uses;
use function iterator_to_array;

trait PaginateWithTotalTestTrait
{
    public function testPaginateWithTotal(): void
    {
        $this->assertInstanceOf(
            PageWithTotal::class,
            $this->db->select()
                ->from('test')
                ->paginateWithTotal()
        );
    }

    public function testPaginateWithTotalCount(): void
    {
        $this->insert();

        $this->assertSame(
            2,
            $this->db->select()
                ->from('test')
                ->paginateWithTotal(1, 2)
                ->count()
        );
    }

    public function testPaginateWithTotalCurrentPage(): void
    {
        $this->assertSame(
            2,
            $this->db->select()
                ->from('test')
                ->paginateWithTotal(2, 2)
                ->currentPage()
        );
    }

    public function testPaginateWithTotalDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(AbstractPage::class)
        );
    }

    public function testPaginateWithTotalFirstItem(): void
    {
        $this->insert();

        $this->assertSame(
            3,
            $this->db->select()
                ->from('test')
                ->paginateWithTotal(2, 2)
                ->firstItem()
        );
    }

    public function testPaginateWithTotalFirstItemEmpty(): void
    {
        $this->assertNull(
            $this->db->select()
                ->from('test')
                ->paginateWithTotal()
                ->firstItem()
        );
    }

    public function testPaginateWithTotalGroupLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Query group limits cannot be used with pagination.');

        $this->db->select()
            ->from('test')
            ->groupLimit(1, 'name')
            ->paginateWithTotal();
    }

    public function testPaginateWithTotalHasNext(): void
    {
        $this->insert();

        $query = $this->db->select()
            ->from('test');

        $this->assertTrue(
            $query->paginateWithTotal(1, 2)
                ->hasNext()
        );
        $this->assertFalse(
            $query->paginateWithTotal(2, 2)
                ->hasNext()
        );
    }

    public function testPaginateWithTotalHasPrevious(): void
    {
        $query = $this->db->select()
            ->from('test');

        $this->assertFalse(
            $query->paginateWithTotal()
                ->hasPrevious()
        );
        $this->assertTrue(
            $query->paginateWithTotal(2)
                ->hasPrevious()
        );
    }

    public function testPaginateWithTotalInvalidPage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Page must be greater than zero.');

        $this->db->select()
            ->from('test')
            ->paginateWithTotal(0);
    }

    public function testPaginateWithTotalInvalidPerPage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Items per page must be greater than zero.');

        $this->db->select()
            ->from('test')
            ->paginateWithTotal(1, 0);
    }

    public function testPaginateWithTotalItems(): void
    {
        $this->insert();

        $this->assertArraysAreIdentical(
            [
                [
                    'id' => 3,
                    'name' => 'Test 3',
                ],
            ],
            $this->db->select()
                ->from('test')
                ->orderBy([
                    'id' => 'ASC',
                ])
                ->paginateWithTotal(2, 2)
                ->items()
        );
    }

    public function testPaginateWithTotalIteration(): void
    {
        $this->insert();

        $page = $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ])
            ->paginateWithTotal(2, 2);

        $this->assertArraysAreIdentical(
            [
                [
                    'id' => 3,
                    'name' => 'Test 3',
                ],
            ],
            iterator_to_array($page)
        );
    }

    public function testPaginateWithTotalJsonSerialize(): void
    {
        $this->insert();

        $page = $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ])
            ->paginateWithTotal(2, 2);

        $this->assertArraysAreIdentical(
            [
                'data' => [
                    [
                        'id' => 3,
                        'name' => 'Test 3',
                    ],
                ],
                'pagination' => [
                    'page' => 2,
                    'perPage' => 2,
                    'total' => 3,
                    'totalPages' => 2,
                ],
            ],
            $page->jsonSerialize()
        );
    }

    public function testPaginateWithTotalLastItem(): void
    {
        $this->insert();

        $this->assertSame(
            3,
            $this->db->select()
                ->from('test')
                ->paginateWithTotal(2, 2)
                ->lastItem()
        );
    }

    public function testPaginateWithTotalLastItemEmpty(): void
    {
        $this->assertNull(
            $this->db->select()
                ->from('test')
                ->paginateWithTotal()
                ->lastItem()
        );
    }

    public function testPaginateWithTotalLazy(): void
    {
        $page = $this->db->select()
            ->from('test')
            ->paginateWithTotal(1, 2);

        $this->insert();

        $this->assertCount(
            2,
            $page
        );
        $this->assertSame(
            3,
            $page->totalItems()
        );
    }

    public function testPaginateWithTotalMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(PageWithTotal::class)
        );
    }

    public function testPaginateWithTotalNextPage(): void
    {
        $this->insert();

        $query = $this->db->select()
            ->from('test');

        $this->assertSame(
            2,
            $query->paginateWithTotal(1, 2)
                ->nextPage()
        );
        $this->assertNull(
            $query->paginateWithTotal(2, 2)
                ->nextPage()
        );
    }

    public function testPaginateWithTotalOverwritesLimitOffset(): void
    {
        $this->insert();

        $query = $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ])
            ->limit(1, 1);

        $page = $query->paginateWithTotal(1, 2);

        $this->assertArraysAreIdentical(
            [
                [
                    'id' => 1,
                    'name' => 'Test 1',
                ],
                [
                    'id' => 2,
                    'name' => 'Test 2',
                ],
            ],
            $page->items()
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

    public function testPaginateWithTotalPerPage(): void
    {
        $this->assertSame(
            10,
            $this->db->select()
                ->from('test')
                ->paginateWithTotal(1, 10)
                ->perPage()
        );
    }

    public function testPaginateWithTotalPreviousPage(): void
    {
        $query = $this->db->select()
            ->from('test');

        $this->assertNull(
            $query->paginateWithTotal()
                ->previousPage()
        );
        $this->assertSame(
            1,
            $query->paginateWithTotal(2)
                ->previousPage()
        );
    }

    public function testPaginateWithTotalTotalItems(): void
    {
        $this->insert();

        $this->assertSame(
            3,
            $this->db->select()
                ->from('test')
                ->paginateWithTotal()
                ->totalItems()
        );
    }

    public function testPaginateWithTotalTotalPages(): void
    {
        $this->insert();

        $this->assertSame(
            2,
            $this->db->select()
                ->from('test')
                ->paginateWithTotal(1, 2)
                ->totalPages()
        );
    }

    public function testPaginateWithTotalTotalPagesEmpty(): void
    {
        $this->assertSame(
            0,
            $this->db->select()
                ->from('test')
                ->paginateWithTotal()
                ->totalPages()
        );
    }
}
