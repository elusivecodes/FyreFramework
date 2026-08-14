<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql\Sql;

use Fyre\DB\Expressions\LiteralExpression;
use Fyre\DB\Queries\SelectQuery;
use Fyre\DB\Query;

trait InsertFromTestTrait
{
    public function testInsertFromClosure(): void
    {
        $query = $this->db->select()
            ->from('test2');

        $this->assertSame(
            'INSERT INTO `test` SELECT * FROM `test2`',
            $this->db->insertFrom(static fn(): SelectQuery => $query)
                ->into('test')
                ->sql()
        );
    }

    public function testInsertFromColumnsClosure(): void
    {
        $query = $this->db->select()
            ->table('test2');

        $this->assertSame(
            'INSERT INTO `test` (`id`, `name`) SELECT * FROM `test2`',
            $this->db->insertFrom(static fn(): SelectQuery => $query, ['id', 'name'])
                ->into('test')
                ->sql()
        );
    }

    public function testInsertFromColumnsLiteral(): void
    {
        $this->assertSame(
            'INSERT INTO `test` (`id`, `name`) SELECT * FROM test2',
            $this->db->insertFrom(static function(Query $query): LiteralExpression {
                return $query->literal('SELECT * FROM test2');
            }, ['id', 'name'])
                ->into('test')
                ->sql()
        );
    }

    public function testInsertFromColumnsSelectQuery(): void
    {
        $query = $this->db->select()
            ->from('test2');

        $this->assertSame(
            'INSERT INTO `test` (`id`, `name`) SELECT * FROM `test2`',
            $this->db->insertFrom($query, ['id', 'name'])
                ->into('test')
                ->sql()
        );
    }

    public function testInsertFromColumnsString(): void
    {
        $this->assertSame(
            'INSERT INTO `test` (`id`, `name`) SELECT * FROM test2',
            $this->db->insertFrom('SELECT * FROM test2', ['id', 'name'])
                ->into('test')
                ->sql()
        );
    }

    public function testInsertFromLiteral(): void
    {
        $this->assertSame(
            'INSERT INTO `test` SELECT * FROM test2',
            $this->db->insertFrom(static function(Query $query): LiteralExpression {
                return $query->literal('SELECT * FROM test2');
            })
                ->into('test')
                ->sql()
        );
    }

    public function testInsertFromSelectQuery(): void
    {
        $query = $this->db->select()
            ->from('test2');

        $this->assertSame(
            'INSERT INTO `test` SELECT * FROM `test2`',
            $this->db->insertFrom($query)
                ->into('test')
                ->sql()
        );
    }

    public function testInsertFromString(): void
    {
        $this->assertSame(
            'INSERT INTO `test` (`id`, `name`) SELECT * FROM test2',
            $this->db->insertFrom('SELECT * FROM test2', ['id', 'name'])
                ->into('test')
                ->sql()
        );
    }
}
