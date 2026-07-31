<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Postgres\Sql;

use BadMethodCallException;
use Fyre\DB\Exceptions\DbException;

trait DeleteTestTrait
{
    public function testDelete(): void
    {
        $this->assertSame(
            'DELETE FROM test',
            $this->db->delete()
                ->from('test')
                ->sql()
        );
    }

    public function testDeleteAlias(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('DELETE queries using aliases are not supported by this connection.');

        $this->db->delete('alt');
    }

    public function testDeleteJoin(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('DELETE queries with a JOIN clause are not supported by this connection.');

        $this->db->delete()
            ->from('test')
            ->join([
                [
                    'table' => 'test2',
                    'conditions' => [
                        'test2.id = test.id',
                    ],
                ],
            ]);
    }

    public function testDeleteLimit(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('DELETE queries with a LIMIT clause are not supported by this connection.');

        $this->db->delete()
            ->from('test')
            ->limit(1);
    }

    public function testDeleteMultipleTables(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionMessage('Multiple tables are not supported for this query.');

        $this->db->delete()
            ->from([
                'alt' => 'test',
                'alt2' => 'test2',
            ]);
    }

    public function testDeleteOrderBy(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('DELETE queries with an ORDER BY clause are not supported by this connection.');

        $this->db->delete()
            ->from('test')
            ->orderBy('id ASC');
    }

    public function testDeleteTables(): void
    {
        $this->assertSame(
            'DELETE FROM test AS alt',
            $this->db->delete()
                ->from([
                    'alt' => 'test',
                ])
                ->sql()
        );
    }

    public function testDeleteUsing(): void
    {
        $this->assertSame(
            'DELETE FROM test USING test2 WHERE test.id = test2.id',
            $this->db->delete()
                ->from('test')
                ->using('test2')
                ->where([
                    'test.id = test2.id',
                ])
                ->sql()
        );
    }

    public function testDeleteWhere(): void
    {
        $this->assertSame(
            'DELETE FROM test WHERE id = 1',
            $this->db->delete()
                ->from('test')
                ->where([
                    'id' => 1,
                ])
                ->sql()
        );
    }
}
