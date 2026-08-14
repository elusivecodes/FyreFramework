<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\MariaDb\Sql;

use Fyre\DB\Expressions\WindowExpression;
use Fyre\DB\Query;

trait LagLeadTestTrait
{
    public function testWindowLagLead(): void
    {
        $this->assertSame(
            'SELECT LAG(`test`.`value`) OVER (PARTITION BY `test`.`group_id` ORDER BY `test`.`id` ASC) AS `previous_value`, LEAD(`test`.`value`, 2) OVER (PARTITION BY `test`.`group_id` ORDER BY `test`.`id` ASC) AS `next_value` FROM `test`',
            $this->db->select([
                'previous_value' => static fn(Query $query): WindowExpression => $query->func()
                    ->lag('test.value')
                    ->partitionBy('test.group_id')
                    ->orderBy([
                        'test.id' => 'ASC',
                    ]),
                'next_value' => static fn(Query $query): WindowExpression => $query->func()
                    ->lead('test.value', 2)
                    ->partitionBy('test.group_id')
                    ->orderBy([
                        'test.id' => 'ASC',
                    ]),
            ])
                ->from('test')
                ->sql()
        );
    }
}
