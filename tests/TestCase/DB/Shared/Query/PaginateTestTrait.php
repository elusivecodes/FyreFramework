<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Shared\Query;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Pagination\Page;
use InvalidArgumentException;

use function array_column;
use function class_uses;

trait PaginateTestTrait
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

    public function testPaginateDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Page::class)
        );
    }

    public function testPaginateGroupLimit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Query group limits cannot be used with pagination.');

        $this->db->select()
            ->from('test')
            ->groupLimit(1, 'name')
            ->paginate();
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

        $this->assertSame(
            [3],
            array_column(
                $this->db->select()
                    ->from('test')
                    ->orderBy([
                        'id' => 'ASC',
                    ])
                    ->paginate(2, 2)
                    ->items(),
                'id'
            )
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
                    'hasNext' => false,
                    'hasPrevious' => true,
                ],
            ],
            $page->jsonSerialize()
        );
    }

    public function testPaginateMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Page::class)
        );
    }

    public function testPaginateNavigation(): void
    {
        $this->insert();

        $query = $this->db->select()
            ->from('test');

        $firstPage = $query->paginate(1, 2);

        $this->assertTrue($firstPage->hasNext());
        $this->assertFalse($firstPage->hasPrevious());
        $this->assertSame(2, $firstPage->nextPage());
        $this->assertNull($firstPage->previousPage());

        $secondPage = $query->paginate(2, 2);

        $this->assertFalse($secondPage->hasNext());
        $this->assertTrue($secondPage->hasPrevious());
        $this->assertNull($secondPage->nextPage());
        $this->assertSame(1, $secondPage->previousPage());
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

        $this->assertSame(
            [1, 2],
            array_column($page->items(), 'id')
        );
        $this->assertSame(1, $query->getLimit());
        $this->assertSame(1, $query->getOffset());
    }

    public function testPaginatePositions(): void
    {
        $this->insert();

        $page = $this->db->select()
            ->from('test')
            ->paginate(2, 2);

        $this->assertSame(2, $page->currentPage());
        $this->assertSame(3, $page->firstItem());
        $this->assertSame(3, $page->lastItem());
        $this->assertSame(2, $page->perPage());
    }

    public function testPaginateUsesSingleQuery(): void
    {
        $this->insert();

        $queries = 0;
        $this->db->getEventManager()->on('Db.query', static function() use (&$queries): void {
            $queries++;
        });

        $this->db->select()
            ->from('test')
            ->paginate(1, 2)
            ->items();

        $this->assertSame(1, $queries);
    }
}
