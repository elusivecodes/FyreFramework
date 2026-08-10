<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Postgres\Sql;

use Fyre\DB\Expressions\LiteralExpression;
use Fyre\DB\Queries\SelectQuery;
use Fyre\DB\Query;

trait UnionTestTrait
{
    public function testUnion(): void
    {
        $this->assertSame(
            '(SELECT * FROM "test") UNION DISTINCT (SELECT * FROM test2)',
            $this->db->select()
                ->from('test')
                ->union('(SELECT * FROM test2)')
                ->sql()
        );
    }

    public function testUnionClosure(): void
    {
        $query = $this->db->select()
            ->from('test2');

        $this->assertSame(
            '(SELECT * FROM "test") UNION DISTINCT (SELECT * FROM "test2")',
            $this->db->select()
                ->from('test')
                ->union(static fn(): SelectQuery => $query)
                ->sql()
        );
    }

    public function testUnionLiteral(): void
    {
        $this->assertSame(
            '(SELECT * FROM "test") UNION DISTINCT (SELECT * FROM test2)',
            $this->db->select()
                ->from('test')
                ->union(static function(Query $query): LiteralExpression {
                    return $query->literal('(SELECT * FROM test2)');
                })
                ->sql()
        );
    }

    public function testUnionMerge(): void
    {
        $this->assertSame(
            '(SELECT * FROM "test") UNION DISTINCT (SELECT * FROM test2) UNION DISTINCT (SELECT * FROM test3)',
            $this->db->select()
                ->from('test')
                ->union('(SELECT * FROM test2)')
                ->union('(SELECT * FROM test3)')
                ->sql()
        );
    }

    public function testUnionOverwrite(): void
    {
        $this->assertSame(
            '(SELECT * FROM "test") UNION DISTINCT (SELECT * FROM test3)',
            $this->db->select()
                ->from('test')
                ->union('(SELECT * FROM test2)')
                ->union('(SELECT * FROM test3)', true)
                ->sql()
        );
    }

    public function testUnionSelectQuery(): void
    {
        $query = $this->db->select()
            ->from('test2');

        $this->assertSame(
            '(SELECT * FROM "test") UNION DISTINCT (SELECT * FROM "test2")',
            $this->db->select()
                ->from('test')
                ->union($query)
                ->sql()
        );
    }
}
