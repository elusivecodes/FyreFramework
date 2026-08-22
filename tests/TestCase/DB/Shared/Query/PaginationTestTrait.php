<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Shared\Query;

use Fyre\DB\Pagination\Page;
use InvalidArgumentException;

use function iterator_to_array;

trait PaginationTestTrait
{
    public function testPaginate(): void
    {
        $this->assertInstanceOf(
            Page::class,
            $this->db->select()
                ->from('test')
                ->paginate()
        );
    }

    public function testPaginateCount(): void
    {
        $this->insert();

        $this->assertSame(
            2,
            $this->db->select()
                ->from('test')
                ->paginate(1, 2)
                ->count()
        );
    }

    public function testPaginateCurrentPage(): void
    {
        $this->assertSame(
            2,
            $this->db->select()
                ->from('test')
                ->paginate(2, 2)
                ->currentPage()
        );
    }

    public function testPaginateFirstItem(): void
    {
        $this->insert();

        $this->assertSame(
            3,
            $this->db->select()
                ->from('test')
                ->paginate(2, 2)
                ->firstItem()
        );
    }

    public function testPaginateFirstItemEmpty(): void
    {
        $this->assertNull(
            $this->db->select()
                ->from('test')
                ->paginate()
                ->firstItem()
        );
    }

    public function testPaginateHasNext(): void
    {
        $this->insert();

        $query = $this->db->select()
            ->from('test');

        $this->assertTrue(
            $query->paginate(1, 2)
                ->hasNext()
        );
        $this->assertFalse(
            $query->paginate(2, 2)
                ->hasNext()
        );
    }

    public function testPaginateHasPrevious(): void
    {
        $query = $this->db->select()
            ->from('test');

        $this->assertFalse(
            $query->paginate()
                ->hasPrevious()
        );
        $this->assertTrue(
            $query->paginate(2)
                ->hasPrevious()
        );
    }

    public function testPaginateInvalidPage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Page must be greater than zero.');

        $this->db->select()
            ->from('test')
            ->paginate(0);
    }

    public function testPaginateInvalidPerPage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Items per page must be greater than zero.');

        $this->db->select()
            ->from('test')
            ->paginate(1, 0);
    }

    public function testPaginateItems(): void
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
                ->paginate(2, 2)
                ->items()
        );
    }

    public function testPaginateIteration(): void
    {
        $this->insert();

        $page = $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ])
            ->paginate(2, 2);

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

    public function testPaginateJsonSerialize(): void
    {
        $this->insert();

        $page = $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ])
            ->paginate(2, 2);

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

    public function testPaginateLastItem(): void
    {
        $this->insert();

        $this->assertSame(
            3,
            $this->db->select()
                ->from('test')
                ->paginate(2, 2)
                ->lastItem()
        );
    }

    public function testPaginateLastItemEmpty(): void
    {
        $this->assertNull(
            $this->db->select()
                ->from('test')
                ->paginate()
                ->lastItem()
        );
    }

    public function testPaginateLazy(): void
    {
        $page = $this->db->select()
            ->from('test')
            ->paginate(1, 2);

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

    public function testPaginateNextPage(): void
    {
        $this->insert();

        $query = $this->db->select()
            ->from('test');

        $this->assertSame(
            2,
            $query->paginate(1, 2)
                ->nextPage()
        );
        $this->assertNull(
            $query->paginate(2, 2)
                ->nextPage()
        );
    }

    public function testPaginateOverwritesLimitOffset(): void
    {
        $this->insert();

        $query = $this->db->select()
            ->from('test')
            ->orderBy([
                'id' => 'ASC',
            ])
            ->limit(1, 1);

        $page = $query->paginate(1, 2);

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

    public function testPaginatePerPage(): void
    {
        $this->assertSame(
            10,
            $this->db->select()
                ->from('test')
                ->paginate(1, 10)
                ->perPage()
        );
    }

    public function testPaginatePreviousPage(): void
    {
        $query = $this->db->select()
            ->from('test');

        $this->assertNull(
            $query->paginate()
                ->previousPage()
        );
        $this->assertSame(
            1,
            $query->paginate(2)
                ->previousPage()
        );
    }

    public function testPaginateTotalItems(): void
    {
        $this->insert();

        $this->assertSame(
            3,
            $this->db->select()
                ->from('test')
                ->paginate()
                ->totalItems()
        );
    }

    public function testPaginateTotalPages(): void
    {
        $this->insert();

        $this->assertSame(
            2,
            $this->db->select()
                ->from('test')
                ->paginate(1, 2)
                ->totalPages()
        );
    }

    public function testPaginateTotalPagesEmpty(): void
    {
        $this->assertSame(
            0,
            $this->db->select()
                ->from('test')
                ->paginate()
                ->totalPages()
        );
    }
}
