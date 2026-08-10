<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql\Sql;

use Fyre\DB\Expressions\LiteralExpression;
use Fyre\DB\Queries\SelectQuery;
use Fyre\DB\Query;
use Fyre\Utility\DateTime\DateTime;

trait InsertTestTrait
{
    public function testInsert(): void
    {
        $this->assertSame(
            'INSERT INTO `test` (`name`, `value`) VALUES (\'Test 1\', 1), (\'Test 2\', 2)',
            $this->db->insert()
                ->into('test')
                ->values([
                    [
                        'name' => 'Test 1',
                        'value' => 1,
                    ],
                    [
                        'name' => 'Test 2',
                        'value' => 2,
                    ],
                ])
                ->sql()
        );
    }

    public function testInsertClosure(): void
    {
        $query = $this->db->select(['id'])
            ->from('test')
            ->limit(1);

        $this->assertSame(
            'INSERT INTO `test` (`name`, `value`) VALUES (\'Test 1\', (SELECT `id` FROM `test` LIMIT 1)), (\'Test 2\', (SELECT `id` FROM `test` LIMIT 1))',
            $this->db->insert()
                ->into('test')
                ->values([
                    [
                        'name' => 'Test 1',
                        'value' => static fn(): SelectQuery => $query,
                    ],
                    [
                        'name' => 'Test 2',
                        'value' => static fn(): SelectQuery => $query,
                    ],
                ])
                ->sql()
        );
    }

    public function testInsertDateTime(): void
    {
        $this->assertSame(
            'INSERT INTO `test` (`name`, `value`) VALUES (\'Test 1\', \'2020-01-01 00:00:00\')',
            $this->db->insert()
                ->into('test')
                ->values([
                    [
                        'name' => 'Test 1',
                        'value' => DateTime::createFromArray([2020, 1, 1]),
                    ],
                ])
                ->sql()
        );
    }

    public function testInsertLiteral(): void
    {
        $this->assertSame(
            'INSERT INTO `test` (`name`, `value`) VALUES (\'Test 1\', 2 * 10), (\'Test 2\', 2 * 20)',
            $this->db->insert()
                ->into('test')
                ->values([
                    [
                        'name' => 'Test 1',
                        'value' => static function(Query $query): LiteralExpression {
                            return $query->literal('2 * 10');
                        },
                    ],
                    [
                        'name' => 'Test 2',
                        'value' => static function(Query $query): LiteralExpression {
                            return $query->literal('2 * 20');
                        },
                    ],
                ])
                ->sql()
        );
    }

    public function testInsertMerge(): void
    {
        $this->assertSame(
            'INSERT INTO `test` (`name`, `value`) VALUES (\'Test 1\', 1), (\'Test 2\', 2)',
            $this->db->insert()
                ->into('test')
                ->values([
                    [
                        'name' => 'Test 1',
                        'value' => 1,
                    ],
                ])
                ->values([
                    [
                        'name' => 'Test 2',
                        'value' => 2,
                    ],
                ])
                ->sql()
        );
    }

    public function testInsertOverwrite(): void
    {
        $this->assertSame(
            'INSERT INTO `test` (`name`, `value`) VALUES (\'Test 2\', 2)',
            $this->db->insert()
                ->into('test')
                ->values([
                    [
                        'name' => 'Test 1',
                        'value' => 1,
                    ],
                ])
                ->values([
                    [
                        'name' => 'Test 2',
                        'value' => 2,
                    ],
                ], true)
                ->sql()
        );
    }

    public function testInsertSelectQuery(): void
    {
        $this->assertSame(
            'INSERT INTO `test` (`name`, `value`) VALUES (\'Test 1\', (SELECT `id` FROM `test` LIMIT 1)), (\'Test 2\', (SELECT `id` FROM `test` LIMIT 1))',
            $this->db->insert()
                ->into('test')
                ->values([
                    [
                        'name' => 'Test 1',
                        'value' => $this->db->select(['id'])
                            ->from('test')
                            ->limit(1),
                    ],
                    [
                        'name' => 'Test 2',
                        'value' => $this->db->select(['id'])
                            ->from('test')
                            ->limit(1),
                    ],
                ])
                ->sql()
        );
    }
}
