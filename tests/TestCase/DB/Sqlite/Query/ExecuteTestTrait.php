<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite\Query;

use Fyre\DB\Expressions\LiteralExpression;
use Fyre\DB\Query;

use function array_column;

trait ExecuteTestTrait
{
    public function testExecuteGeneratedHavingOrderAndLimit(): void
    {
        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test',
                ],
                [
                    'name' => 'Test',
                ],
                [
                    'name' => 'Other',
                ],
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

    public function testExecuteGroupLimit(): void
    {
        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test',
                ],
                [
                    'name' => 'Test',
                ],
                [
                    'name' => 'Other',
                ],
            ])
            ->execute();

        $result = $this->db->select([
            'id',
            'name',
        ])
            ->from('test')
            ->orderBy('id')
            ->groupLimit(1, 'name')
            ->execute();

        $rows = array_column($result->all(), 'id', 'name');

        $this->assertSame(
            1,
            $rows['Test']
        );

        $this->assertSame(
            3,
            $rows['Other']
        );
    }
}
