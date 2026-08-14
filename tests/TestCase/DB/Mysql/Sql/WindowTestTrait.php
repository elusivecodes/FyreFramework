<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql\Sql;

use Fyre\DB\Expressions\WindowExpression;
use Fyre\DB\Query;

trait WindowTestTrait
{
    public function testWindowCumeDist(): void
    {
        $this->assertSame(
            'SELECT CUME_DIST() OVER (ORDER BY `test`.`value` ASC) AS `distribution` FROM `test`',
            $this->db->select([
                'distribution' => static fn(Query $query): WindowExpression => $query->func()
                    ->cumeDist()
                    ->orderBy([
                        'test.value' => 'ASC',
                    ]),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testWindowDenseRank(): void
    {
        $this->assertSame(
            'SELECT DENSE_RANK() OVER (ORDER BY `test`.`value` ASC) AS `rank` FROM `test`',
            $this->db->select([
                'rank' => static fn(Query $query): WindowExpression => $query->func()
                    ->denseRank()
                    ->orderBy([
                        'test.value' => 'ASC',
                    ]),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testWindowEmpty(): void
    {
        $this->assertSame(
            'SELECT RANK() OVER () AS `rank` FROM `test`',
            $this->db->select([
                'rank' => static fn(Query $query): WindowExpression => $query->func()
                    ->rank(),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testWindowFirstValue(): void
    {
        $this->assertSame(
            'SELECT FIRST_VALUE(`test`.`value`) OVER (ORDER BY `test`.`id` ASC) AS `first_value` FROM `test`',
            $this->db->select([
                'first_value' => static fn(Query $query): WindowExpression => $query->func()
                    ->firstValue('test.value')
                    ->orderBy([
                        'test.id' => 'ASC',
                    ]),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testWindowLastValue(): void
    {
        $this->assertSame(
            'SELECT LAST_VALUE(`test`.`value`) OVER (ORDER BY `test`.`id` ASC) AS `last_value` FROM `test`',
            $this->db->select([
                'last_value' => static fn(Query $query): WindowExpression => $query->func()
                    ->lastValue('test.value')
                    ->orderBy([
                        'test.id' => 'ASC',
                    ]),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testWindowMultiplePartitions(): void
    {
        $this->assertSame(
            'SELECT ROW_NUMBER() OVER (PARTITION BY `test`.`group_id`, `test`.`type` ORDER BY `test`.`id` ASC) AS `row_number` FROM `test`',
            $this->db->select([
                'row_number' => static fn(Query $query): WindowExpression => $query->func()
                    ->rowNumber()
                    ->partitionBy([
                        'test.group_id',
                        'test.type',
                    ])
                    ->orderBy([
                        'test.id' => 'ASC',
                    ]),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testWindowNthValue(): void
    {
        $this->assertSame(
            'SELECT NTH_VALUE(`test`.`value`, 2) OVER (ORDER BY `test`.`id` ASC) AS `value` FROM `test`',
            $this->db->select([
                'value' => static fn(Query $query): WindowExpression => $query->func()
                    ->nthValue('test.value', 2)
                    ->orderBy([
                        'test.id' => 'ASC',
                    ]),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testWindowNtile(): void
    {
        $this->assertSame(
            'SELECT NTILE(4) OVER (ORDER BY `test`.`value` ASC) AS `bucket` FROM `test`',
            $this->db->select([
                'bucket' => static fn(Query $query): WindowExpression => $query->func()
                    ->ntile(4)
                    ->orderBy([
                        'test.value' => 'ASC',
                    ]),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testWindowPercentRank(): void
    {
        $this->assertSame(
            'SELECT PERCENT_RANK() OVER (ORDER BY `test`.`value` ASC) AS `rank` FROM `test`',
            $this->db->select([
                'rank' => static fn(Query $query): WindowExpression => $query->func()
                    ->percentRank()
                    ->orderBy([
                        'test.value' => 'ASC',
                    ]),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testWindowRange(): void
    {
        $this->assertSame(
            'SELECT FIRST_VALUE(`test`.`value`) OVER (ORDER BY `test`.`id` ASC RANGE BETWEEN 2 PRECEDING AND 1 FOLLOWING) AS `value` FROM `test`',
            $this->db->select([
                'value' => static fn(Query $query): WindowExpression => $query->func()
                    ->firstValue('test.value')
                    ->orderBy([
                        'test.id' => 'ASC',
                    ])
                    ->range(2, 1),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testWindowRowNumber(): void
    {
        $this->assertSame(
            'SELECT ROW_NUMBER() OVER (PARTITION BY `test`.`group_id` ORDER BY `test`.`id` ASC) AS `row_number` FROM `test`',
            $this->db->select([
                'row_number' => static fn(Query $query): WindowExpression => $query->func()
                    ->rowNumber()
                    ->partitionBy('test.group_id')
                    ->orderBy([
                        'test.id' => 'ASC',
                    ]),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testWindowRows(): void
    {
        $this->assertSame(
            'SELECT FIRST_VALUE(`test`.`value`) OVER (ORDER BY `test`.`id` ASC ROWS BETWEEN 2 PRECEDING AND 1 FOLLOWING) AS `value` FROM `test`',
            $this->db->select([
                'value' => static fn(Query $query): WindowExpression => $query->func()
                    ->firstValue('test.value')
                    ->orderBy([
                        'test.id' => 'ASC',
                    ])
                    ->rows(2, 1),
            ])
                ->from('test')
                ->sql()
        );
    }
}
