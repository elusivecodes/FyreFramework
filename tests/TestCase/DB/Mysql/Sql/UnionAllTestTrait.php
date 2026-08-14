<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql\Sql;

use Fyre\DB\Expressions\LiteralExpression;
use Fyre\DB\Queries\SelectQuery;
use Fyre\DB\Query;

trait UnionAllTestTrait
{
    public function testUnionAll(): void
    {
        $this->assertSame(
            '(SELECT * FROM `test`) UNION ALL (SELECT * FROM test2)',
            $this->db->select()
                ->from('test')
                ->unionAll('(SELECT * FROM test2)')
                ->sql()
        );
    }

    public function testUnionAllClosure(): void
    {
        $query = $this->db->select()
            ->from('test2');

        $this->assertSame(
            '(SELECT * FROM `test`) UNION ALL (SELECT * FROM `test2`)',
            $this->db->select()
                ->from('test')
                ->unionAll(static fn(): SelectQuery => $query)
                ->sql()
        );
    }

    public function testUnionAllLiteral(): void
    {
        $this->assertSame(
            '(SELECT * FROM `test`) UNION ALL (SELECT * FROM test2)',
            $this->db->select()
                ->from('test')
                ->unionAll(static function(Query $query): LiteralExpression {
                    return $query->literal('(SELECT * FROM test2)');
                })
                ->sql()
        );
    }

    public function testUnionAllMerge(): void
    {
        $this->assertSame(
            '(SELECT * FROM `test`) UNION ALL (SELECT * FROM test2) UNION ALL (SELECT * FROM test3)',
            $this->db->select()
                ->from('test')
                ->unionAll('(SELECT * FROM test2)')
                ->unionAll('(SELECT * FROM test3)')
                ->sql()
        );
    }

    public function testUnionAllOverwrite(): void
    {
        $this->assertSame(
            '(SELECT * FROM `test`) UNION ALL (SELECT * FROM test3)',
            $this->db->select()
                ->from('test')
                ->unionAll('(SELECT * FROM test2)')
                ->unionAll('(SELECT * FROM test3)', true)
                ->sql()
        );
    }

    public function testUnionAllSelectQuery(): void
    {
        $query = $this->db->select()
            ->from('test2');

        $this->assertSame(
            '(SELECT * FROM `test`) UNION ALL (SELECT * FROM `test2`)',
            $this->db->select()
                ->from('test')
                ->unionAll($query)
                ->sql()
        );
    }
}
