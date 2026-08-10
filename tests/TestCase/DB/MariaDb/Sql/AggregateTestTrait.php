<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\MariaDb\Sql;

use Fyre\DB\Expressions\AggregateExpression;
use Fyre\DB\Expressions\WindowExpression;
use Fyre\DB\Query;

trait AggregateTestTrait
{
    public function testAggregateAverage(): void
    {
        $this->assertSame(
            'SELECT AVG(`test`.`value`) AS `average` FROM `test`',
            $this->db->select([
                'average' => static fn(Query $query): AggregateExpression => $query->func()
                    ->avg('test.value'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testAggregateCount(): void
    {
        $this->assertSame(
            'SELECT COUNT(*) AS `total` FROM `test`',
            $this->db->select([
                'total' => static fn(Query $query): AggregateExpression => $query->func()
                    ->count(),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testAggregateDistinct(): void
    {
        $this->assertSame(
            'SELECT COUNT(DISTINCT `test`.`value`) AS `total` FROM `test`',
            $this->db->select([
                'total' => static fn(Query $query): AggregateExpression => $query->func()
                    ->count('test.value')
                    ->distinct(),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testAggregateFilter(): void
    {
        $this->assertSame(
            'SELECT COUNT(CASE WHEN `test`.`active` = 1 THEN 1 END) AS `total` FROM `test`',
            $this->db->select([
                'total' => static fn(Query $query): AggregateExpression => $query->func()
                    ->count()
                    ->filter($query->expr()->eq('test.active', true)),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testAggregateMax(): void
    {
        $this->assertSame(
            'SELECT MAX(`test`.`value`) AS `maximum` FROM `test`',
            $this->db->select([
                'maximum' => static fn(Query $query): AggregateExpression => $query->func()
                    ->max('test.value'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testAggregateMin(): void
    {
        $this->assertSame(
            'SELECT MIN(`test`.`value`) AS `minimum` FROM `test`',
            $this->db->select([
                'minimum' => static fn(Query $query): AggregateExpression => $query->func()
                    ->min('test.value'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testAggregateSum(): void
    {
        $this->assertSame(
            'SELECT SUM(`test`.`value`) AS `total` FROM `test`',
            $this->db->select([
                'total' => static fn(Query $query): AggregateExpression => $query->func()
                    ->sum('test.value'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testAggregateWindow(): void
    {
        $this->assertSame(
            'SELECT SUM(DISTINCT CASE WHEN `test`.`active` = 1 THEN `test`.`total` END) OVER (PARTITION BY `test`.`group_id` ORDER BY `test`.`id` ASC ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW) AS `total` FROM `test`',
            $this->db->select([
                'total' => static fn(Query $query): WindowExpression => $query->func()
                    ->sum('test.total')
                    ->distinct()
                    ->filter($query->expr()->eq('test.active', true))
                    ->over()
                    ->partitionBy('test.group_id')
                    ->orderBy([
                        'test.id' => 'ASC',
                    ])
                    ->rows(null),
            ])
                ->from('test')
                ->sql()
        );
    }
}
