<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite\Sql;

use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Expressions\LiteralExpression;
use Fyre\DB\Queries\SelectQuery;
use Fyre\DB\Query;
use Fyre\Utility\DateTime\DateTime;
use PHPUnit\Framework\Attributes\DataProvider;

trait HavingTestTrait
{
    /**
     * @return array<string, array{string, array<array-key, mixed>}>
     */
    public static function havingComparisonProvider(): array
    {
        return [
            'equal' => ['SELECT * FROM "test" HAVING "value" = 1', ['value =' => 1]],
            'not equal' => ['SELECT * FROM "test" HAVING "value" != 1', ['value !=' => 1]],
            'greater than' => ['SELECT * FROM "test" HAVING "value" > 1', ['value >' => 1]],
            'greater than or equal' => ['SELECT * FROM "test" HAVING "value" >= 1', ['value >=' => 1]],
            'less than' => ['SELECT * FROM "test" HAVING "value" < 1', ['value <' => 1]],
            'less than or equal' => ['SELECT * FROM "test" HAVING "value" <= 1', ['value <=' => 1]],
        ];
    }

    /**
     * @return array<string, array{string, array<array-key, mixed>}>
     */
    public static function havingLogicalProvider(): array
    {
        return [
            'and' => [
                'SELECT * FROM "test" HAVING "value" = 1 AND "name" = \'test\'',
                [
                    'and' => ['value' => 1, 'name' => 'test'],
                ],
            ],
            'or' => [
                'SELECT * FROM "test" HAVING "value" = 1 OR "name" = \'test\'',
                [
                    'or' => ['value' => 1, 'name' => 'test'],
                ],
            ],
            'not' => [
                'SELECT * FROM "test" HAVING NOT ("value" = 1 AND "name" = \'test\')',
                [
                    'not' => ['value' => 1, 'name' => 'test'],
                ],
            ],
            'groups' => [
                'SELECT * FROM "test" HAVING "value" = 1 AND ("name" = \'test\' OR name IS NULL)',
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
    public static function havingScalarProvider(): array
    {
        return [
            'boolean false' => ['SELECT * FROM "test" HAVING "value" = 0', ['value' => false]],
            'boolean true' => ['SELECT * FROM "test" HAVING "value" = 1', ['value' => true]],
            'float' => ['SELECT * FROM "test" HAVING "value" = 1.25', ['value' => 1.25]],
            'integer' => ['SELECT * FROM "test" HAVING "id" = 1', ['id' => 1]],
        ];
    }

    public function testHaving(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" HAVING name IS NULL',
            $this->db->select()
                ->from('test')
                ->having('name IS NULL')
                ->sql()
        );
    }

    public function testHavingArray(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" HAVING "name" = \'test\'',
            $this->db->select()
                ->from('test')
                ->having([
                    'name' => 'test',
                ])
                ->sql()
        );
    }

    public function testHavingClosure(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" HAVING "value" = 1',
            $this->db->select()
                ->from('test')
                ->having(static fn(Query $query): ConditionExpression => $query->expr()
                    ->eq('value', 1))
                ->sql()
        );
    }

    public function testHavingClosureValue(): void
    {
        $query = $this->db->select(['id'])
            ->from('test');

        $this->assertSame(
            'SELECT * FROM "test" HAVING "value" IN (SELECT "id" FROM "test")',
            $this->db->select()
                ->from('test')
                ->having([
                    'value IN' => static fn(): SelectQuery => $query,
                ])
                ->sql()
        );
    }

    /**
     * @param array<array-key, mixed> $conditions
     */
    #[DataProvider('havingComparisonProvider')]
    public function testHavingComparison(string $expected, array $conditions): void
    {
        $this->assertSame(
            $expected,
            $this->db->select()
                ->from('test')
                ->having($conditions)
                ->sql()
        );
    }

    public function testHavingDateTime(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" HAVING "value" = \'2020-01-01 00:00:00\'',
            $this->db->select()
                ->from('test')
                ->having([
                    'value' => DateTime::createFromArray([2020, 1, 1]),
                ])
                ->sql()
        );
    }

    public function testHavingIn(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" HAVING "value" IN (1, 2, 3)',
            $this->db->select()
                ->from('test')
                ->having([
                    'value IN' => [1, 2, 3],
                ])
                ->sql()
        );
    }

    public function testHavingIsNotNull(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" HAVING "value" IS NOT NULL',
            $this->db->select()
                ->from('test')
                ->having([
                    'value IS NOT' => null,
                ])
                ->sql()
        );
    }

    public function testHavingIsNull(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" HAVING "value" IS NULL',
            $this->db->select()
                ->from('test')
                ->having([
                    'value IS' => null,
                ])
                ->sql()
        );
    }

    public function testHavingLike(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" HAVING "name" LIKE \'%test%\'',
            $this->db->select()
                ->from('test')
                ->having([
                    'name LIKE' => '%test%',
                ])
                ->sql()
        );
    }

    public function testHavingLiteral(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" HAVING "value" = UPPER(test)',
            $this->db->select()
                ->from('test')
                ->having([
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
    #[DataProvider('havingLogicalProvider')]
    public function testHavingLogical(string $expected, array $conditions): void
    {
        $this->assertSame(
            $expected,
            $this->db->select()
                ->from('test')
                ->having($conditions)
                ->sql()
        );
    }

    public function testHavingMerge(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" HAVING "name" = \'test\' AND "value" = 1',
            $this->db->select()
                ->from('test')
                ->having([
                    'name' => 'test',
                ])
                ->having([
                    'value' => 1,
                ])
                ->sql()
        );
    }

    public function testHavingMultiple(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" HAVING "value" = 1 AND "name" = \'test\'',
            $this->db->select()
                ->from('test')
                ->having([
                    'value' => 1,
                    'name' => 'test',
                ])
                ->sql()
        );
    }

    public function testHavingNotIn(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" HAVING "value" NOT IN (1, 2, 3)',
            $this->db->select()
                ->from('test')
                ->having([
                    'value NOT IN' => [1, 2, 3],
                ])
                ->sql()
        );
    }

    public function testHavingNotLike(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" HAVING "name" NOT LIKE \'%test%\'',
            $this->db->select()
                ->from('test')
                ->having([
                    'name NOT LIKE' => '%test%',
                ])
                ->sql()
        );
    }

    public function testHavingOverwrite(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" HAVING "value" = 1',
            $this->db->select()
                ->from('test')
                ->having([
                    'name' => 'test',
                ])
                ->having([
                    'value' => 1,
                ], true)
                ->sql()
        );
    }

    /**
     * @param array<array-key, mixed> $conditions
     */
    #[DataProvider('havingScalarProvider')]
    public function testHavingScalar(string $expected, array $conditions): void
    {
        $this->assertSame(
            $expected,
            $this->db->select()
                ->from('test')
                ->having($conditions)
                ->sql()
        );
    }

    public function testHavingSelectQuery(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" HAVING "value" IN (SELECT "id" FROM "test")',
            $this->db->select()
                ->from('test')
                ->having([
                    'value IN' => $this->db->select(['id'])
                        ->from('test'),
                ])
                ->sql()
        );
    }
}
