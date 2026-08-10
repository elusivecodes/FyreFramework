<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Postgres\Sql;

use Fyre\DB\Expressions\LiteralExpression;
use Fyre\DB\Queries\SelectQuery;
use Fyre\DB\Query;

trait IntersectTestTrait
{
    public function testIntersect(): void
    {
        $this->assertSame(
            '(SELECT * FROM "test") INTERSECT (SELECT * FROM test2)',
            $this->db->select()
                ->from('test')
                ->intersect('(SELECT * FROM test2)')
                ->sql()
        );
    }

    public function testIntersectClosure(): void
    {
        $query = $this->db->select()
            ->from('test2');

        $query = $this->db->select()
            ->from('test2');

        $this->assertSame(
            '(SELECT * FROM "test") INTERSECT (SELECT * FROM "test2")',
            $this->db->select()
                ->from('test')
                ->intersect(static fn(): SelectQuery => $query)
                ->sql()
        );
    }

    public function testIntersectLiteral(): void
    {
        $query = $this->db->select()
            ->from('test2');

        $this->assertSame(
            '(SELECT * FROM "test") INTERSECT (SELECT * FROM test2)',
            $this->db->select()
                ->from('test')
                ->intersect(static function(Query $query): LiteralExpression {
                    return $query->literal('(SELECT * FROM test2)');
                })
                ->sql()
        );
    }

    public function testIntersectMerge(): void
    {
        $this->assertSame(
            '(SELECT * FROM "test") INTERSECT (SELECT * FROM test2) INTERSECT (SELECT * FROM test3)',
            $this->db->select()
                ->from('test')
                ->intersect('(SELECT * FROM test2)')
                ->intersect('(SELECT * FROM test3)')
                ->sql()
        );
    }

    public function testIntersectOverwrite(): void
    {
        $this->assertSame(
            '(SELECT * FROM "test") INTERSECT (SELECT * FROM test3)',
            $this->db->select()
                ->from('test')
                ->intersect('(SELECT * FROM test2)')
                ->intersect('(SELECT * FROM test3)', true)
                ->sql()
        );
    }

    public function testIntersectSelectQuery(): void
    {
        $query = $this->db->select()
            ->from('test2');

        $this->assertSame(
            '(SELECT * FROM "test") INTERSECT (SELECT * FROM "test2")',
            $this->db->select()
                ->from('test')
                ->intersect($query)
                ->sql()
        );
    }
}
