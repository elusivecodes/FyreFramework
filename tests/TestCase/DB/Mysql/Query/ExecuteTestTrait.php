<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql\Query;

use Fyre\DB\Expressions\LiteralExpression;
use Fyre\DB\Query;

use function array_column;

trait ExecuteTestTrait
{
    public function testExecute(): void
    {
        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test 1',
                ],
                [
                    'name' => 'Test 2',
                ],
            ])
            ->execute();

        $this->assertSame(
            [
                'id' => 2,
                'name' => 'Test 2',
            ],
            $this->db->execute('SELECT * FROM test WHERE name = ?', ['Test 2'])
                ->first()
        );
    }

    public function testExecuteGeneratedHavingOrderAndLimit(): void
    {
        $this->db->insert()
            ->into('test')
            ->values([
                ['name' => 'Test'],
                ['name' => 'Test'],
                ['name' => 'Other'],
            ])
            ->execute();

        $rows = $this->db->select([
            'name',
            'total' => static fn(Query $query): LiteralExpression => $query->literal('COUNT(*)'),
        ])
            ->from('test')
            ->groupBy('name')
            ->having(['COUNT(*) >' => 0])
            ->orderBy('name ASC')
            ->limit(1, 1)
            ->execute()
            ->all();

        $this->assertSame(['Test'], array_column($rows, 'name'));
    }

    public function testExecuteNamed(): void
    {
        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test 1',
                ],
                [
                    'name' => 'Test 2',
                ],
            ])
            ->execute();

        $this->assertSame(
            [
                'id' => 2,
                'name' => 'Test 2',
            ],
            $this->db->execute('SELECT * FROM test WHERE name = :name', ['name' => 'Test 2'])
                ->first()
        );
    }

    public function testExecuteUpdate(): void
    {
        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test',
                ],
            ])
            ->execute();

        $this->db->execute('UPDATE test SET name = ? WHERE name = ?', ['Test 2', 'Test']);

        $this->assertSame(
            1,
            $this->db->affectedRows()
        );
    }
}
