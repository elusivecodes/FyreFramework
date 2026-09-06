<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql\Sql;

use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Expressions\LiteralExpression;
use Fyre\DB\Queries\SelectQuery;
use Fyre\DB\Query;
use Fyre\Utility\DateTime\Date;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\DateTime\Time;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Mock\Enums\State;
use Tests\Mock\Enums\Status;

trait WhereTestTrait
{
    /**
     * @return array<string, array{string, array<array-key, mixed>}>
     */
    public static function whereComparisonProvider(): array
    {
        return [
            'equal' => ['SELECT * FROM `test` WHERE `value` = 1', ['value =' => 1]],
            'not equal' => ['SELECT * FROM `test` WHERE `value` != 1', ['value !=' => 1]],
            'greater than' => ['SELECT * FROM `test` WHERE `value` > 1', ['value >' => 1]],
            'greater than or equal' => ['SELECT * FROM `test` WHERE `value` >= 1', ['value >=' => 1]],
            'less than' => ['SELECT * FROM `test` WHERE `value` < 1', ['value <' => 1]],
            'less than or equal' => ['SELECT * FROM `test` WHERE `value` <= 1', ['value <=' => 1]],
        ];
    }

    /**
     * @return array<string, array{string, array<array-key, mixed>}>
     */
    public static function whereLogicalProvider(): array
    {
        return [
            'and' => [
                'SELECT * FROM `test` WHERE `value` = 1 AND `name` = \'test\'',
                [
                    'and' => ['value' => 1, 'name' => 'test'],
                ],
            ],
            'or' => [
                'SELECT * FROM `test` WHERE `value` = 1 OR `name` = \'test\'',
                [
                    'or' => ['value' => 1, 'name' => 'test'],
                ],
            ],
            'not' => [
                'SELECT * FROM `test` WHERE NOT (`value` = 1 AND `name` = \'test\')',
                [
                    'not' => ['value' => 1, 'name' => 'test'],
                ],
            ],
            'groups' => [
                'SELECT * FROM `test` WHERE `value` = 1 AND (`name` = \'test\' OR name IS NULL)',
                [
                    [
                        'value' => 1,
                        'or' => ['name' => 'test', 'name IS NULL'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{string, array<array-key, mixed>}>
     */
    public static function whereScalarProvider(): array
    {
        return [
            'boolean false' => ['SELECT * FROM `test` WHERE `value` = 0', ['value' => false]],
            'boolean true' => ['SELECT * FROM `test` WHERE `value` = 1', ['value' => true]],
            'float' => ['SELECT * FROM `test` WHERE `value` = 1.25', ['value' => 1.25]],
            'integer' => ['SELECT * FROM `test` WHERE `id` = 1', ['id' => 1]],
        ];
    }

    public function testWhere(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE name IS NULL',
            $this->db->select()
                ->from('test')
                ->where('name IS NULL')
                ->sql()
        );
    }

    public function testWhereArray(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `name` = \'test\'',
            $this->db->select()
                ->from('test')
                ->where([
                    'name' => 'test',
                ])
                ->sql()
        );
    }

    public function testWhereClosure(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `value` = 1',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->eq('value', 1))
                ->sql()
        );
    }

    public function testWhereClosureValue(): void
    {
        $query = $this->db->select(['id'])
            ->from('test');

        $this->assertSame(
            'SELECT * FROM `test` WHERE `value` IN (SELECT `id` FROM `test`)',
            $this->db->select()
                ->from('test')
                ->where([
                    'value IN' => static fn(): SelectQuery => $query,
                ])
                ->sql()
        );
    }

    /**
     * @param array<array-key, mixed> $conditions
     */
    #[DataProvider('whereComparisonProvider')]
    public function testWhereComparison(string $expected, array $conditions): void
    {
        $this->assertSame(
            $expected,
            $this->db->select()
                ->from('test')
                ->where($conditions)
                ->sql()
        );
    }

    public function testWhereDate(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `value` = \'2020-01-02\'',
            $this->db->select()
                ->from('test')
                ->where([
                    'value' => Date::createFromArray([2020, 1, 2]),
                ])
                ->sql()
        );
    }

    public function testWhereDateTime(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `value` = \'2020-01-01 00:00:00\'',
            $this->db->select()
                ->from('test')
                ->where([
                    'value' => DateTime::createFromArray([2020, 1, 1]),
                ])
                ->sql()
        );
    }

    public function testWhereEnum(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `status` = \'draft\' AND `state` = \'Published\'',
            $this->db->select()
                ->from('test')
                ->where([
                    'status' => Status::Draft,
                    'state' => State::Published,
                ])
                ->sql()
        );
    }

    public function testWhereIn(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `value` IN (1, 2, 3)',
            $this->db->select()
                ->from('test')
                ->where([
                    'value IN' => [1, 2, 3],
                ])
                ->sql()
        );
    }

    public function testWhereIsNotNull(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `value` IS NOT NULL',
            $this->db->select()
                ->from('test')
                ->where([
                    'value IS NOT' => null,
                ])
                ->sql()
        );
    }

    public function testWhereIsNull(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `value` IS NULL',
            $this->db->select()
                ->from('test')
                ->where([
                    'value IS' => null,
                ])
                ->sql()
        );
    }

    public function testWhereLike(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `name` LIKE \'%test%\'',
            $this->db->select()
                ->from('test')
                ->where([
                    'name LIKE' => '%test%',
                ])
                ->sql()
        );
    }

    public function testWhereLiteral(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `value` = UPPER(test)',
            $this->db->select()
                ->from('test')
                ->where([
                    'value' => static function(Query $query): LiteralExpression {
                        return $query->literal('UPPER(test)');
                    },
                ])
                ->sql()
        );
    }

    /**
     * @param array<array-key, mixed> $conditions
     */
    #[DataProvider('whereLogicalProvider')]
    public function testWhereLogical(string $expected, array $conditions): void
    {
        $this->assertSame(
            $expected,
            $this->db->select()
                ->from('test')
                ->where($conditions)
                ->sql()
        );
    }

    public function testWhereMerge(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `name` = \'test\' AND `value` = 1',
            $this->db->select()
                ->from('test')
                ->where([
                    'name' => 'test',
                ])
                ->where([
                    'value' => 1,
                ])
                ->sql()
        );
    }

    public function testWhereMultiple(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `value` = 1 AND `name` = \'test\'',
            $this->db->select()
                ->from('test')
                ->where([
                    'value' => 1,
                    'name' => 'test',
                ])
                ->sql()
        );
    }

    public function testWhereNotIn(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `value` NOT IN (1, 2, 3)',
            $this->db->select()
                ->from('test')
                ->where([
                    'value NOT IN' => [1, 2, 3],
                ])
                ->sql()
        );
    }

    public function testWhereNotLike(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `name` NOT LIKE \'%test%\'',
            $this->db->select()
                ->from('test')
                ->where([
                    'name NOT LIKE' => '%test%',
                ])
                ->sql()
        );
    }

    public function testWhereOverwrite(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `value` = 1',
            $this->db->select()
                ->from('test')
                ->where([
                    'name' => 'test',
                ])
                ->where([
                    'value' => 1,
                ], true)
                ->sql()
        );
    }

    /**
     * @param array<array-key, mixed> $conditions
     */
    #[DataProvider('whereScalarProvider')]
    public function testWhereScalar(string $expected, array $conditions): void
    {
        $this->assertSame(
            $expected,
            $this->db->select()
                ->from('test')
                ->where($conditions)
                ->sql()
        );
    }

    public function testWhereSelectQuery(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `value` IN (SELECT `id` FROM `test`)',
            $this->db->select()
                ->from('test')
                ->where([
                    'value IN' => $this->db->select(['id'])
                        ->from('test'),
                ])
                ->sql()
        );
    }

    public function testWhereTime(): void
    {
        $this->assertSame(
            'SELECT * FROM `test` WHERE `value` = \'12:30:15.000\'',
            $this->db->select()
                ->from('test')
                ->where([
                    'value' => Time::createFromArray([12, 30, 15]),
                ])
                ->sql()
        );
    }
}
