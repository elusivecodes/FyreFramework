<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite\Sql;

use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Query;
use InvalidArgumentException;

trait ConditionTestTrait
{
    public function testConditionBetween(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" BETWEEN 1 AND 5',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->between('value', 1, 5))
                ->sql()
        );
    }

    public function testConditionEmptyExpression(): void
    {
        $this->assertSame(
            'SELECT * FROM "test"',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr())
                ->sql()
        );
    }

    public function testConditionEmptyExpressionWithArraySibling(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "active" = 1',
            $this->db->select()
                ->from('test')
                ->where([
                    'active' => 1,
                    static fn(Query $query): ConditionExpression => $query->expr(),
                ])
                ->sql()
        );
    }

    public function testConditionEqual(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" = 1',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->eq('value', 1))
                ->sql()
        );
    }

    public function testConditionEqualFields(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "test"."value" = "test2"."value"',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->equalFields('test.value', 'test2.value'))
                ->sql()
        );
    }

    public function testConditionExists(): void
    {
        $values = $this->db->select(['id'])
            ->from('test2');

        $this->assertSame(
            'SELECT * FROM "test" WHERE EXISTS (SELECT "id" FROM "test2")',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->exists($values))
                ->sql()
        );
    }

    public function testConditionExpressionOperands(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE LOWER("test"."value") = LOWER("test2"."value")',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->eq(
                        $query->func()->lower('test.value'),
                        $query->func()->lower('test2.value')
                    ))
                ->sql()
        );
    }

    public function testConditionExpressionWithArraySiblings(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "active" = 1 AND ("status" = \'pending\' OR "status" = \'queued\')',
            $this->db->select()
                ->from('test')
                ->where([
                    'active' => 1,
                    static fn(Query $query): ConditionExpression => $query->expr()->or()
                        ->eq('status', 'pending')
                        ->eq('status', 'queued'),
                ])
                ->sql()
        );
    }

    public function testConditionGreaterThan(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" > 1',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->gt('value', 1))
                ->sql()
        );
    }

    public function testConditionGreaterThanOrEqual(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" >= 1',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->gte('value', 1))
                ->sql()
        );
    }

    public function testConditionIn(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" IN (1, 2)',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->in('value', [1, 2]))
                ->sql()
        );
    }

    public function testConditionInEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query condition IN values must not be empty.');

        $this->db->select()
            ->from('test')
            ->where(['value IN' => []])
            ->sql();
    }

    public function testConditionInOrNull(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" IN (1, 2) OR "value" IS NULL',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->inOrNull('value', [1, 2]))
                ->sql()
        );
    }

    public function testConditionInSelectQuery(): void
    {
        $values = $this->db->select(['id'])
            ->from('test2');

        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" IN (SELECT "id" FROM "test2")',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->in('value', $values))
                ->sql()
        );
    }

    public function testConditionIsDistinctFrom(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" IS NOT 1',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->isDistinctFrom('value', 1))
                ->sql()
        );
    }

    public function testConditionIsNotDistinctFrom(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" IS 1',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->isNotDistinctFrom('value', 1))
                ->sql()
        );
    }

    public function testConditionIsNotNull(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" IS NOT NULL',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->isNotNull('value'))
                ->sql()
        );
    }

    public function testConditionIsNull(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" IS NULL',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->isNull('value'))
                ->sql()
        );
    }

    public function testConditionLessThan(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" < 1',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->lt('value', 1))
                ->sql()
        );
    }

    public function testConditionLessThanOrEqual(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" <= 1',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->lte('value', 1))
                ->sql()
        );
    }

    public function testConditionLike(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" LIKE \'test%\'',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->like('value', 'test%'))
                ->sql()
        );
    }

    public function testConditionNested(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE ("active" = 1) AND (("status" = \'pending\') OR ("status" = \'queued\'))',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->and(
                        $query->expr()->eq('active', true),
                        $query->expr()->or(
                            $query->expr()->eq('status', 'pending'),
                            $query->expr()->eq('status', 'queued')
                        )
                    ))
                ->sql()
        );
    }

    public function testConditionNot(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE NOT ("value" = 1)',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->not($query->expr()->eq('value', 1)))
                ->sql()
        );
    }

    public function testConditionNotBetween(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" NOT BETWEEN 1 AND 5',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->notBetween('value', 1, 5))
                ->sql()
        );
    }

    public function testConditionNotEqual(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" != 1',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->notEq('value', 1))
                ->sql()
        );
    }

    public function testConditionNotExists(): void
    {
        $values = $this->db->select(['id'])
            ->from('test2');

        $this->assertSame(
            'SELECT * FROM "test" WHERE NOT EXISTS (SELECT "id" FROM "test2")',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->notExists($values))
                ->sql()
        );
    }

    public function testConditionNotIn(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" NOT IN (1, 2)',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->notIn('value', [1, 2]))
                ->sql()
        );
    }

    public function testConditionNotInOrNull(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" NOT IN (1, 2) OR "value" IS NULL',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->notInOrNull('value', [1, 2]))
                ->sql()
        );
    }

    public function testConditionNotInSelectQuery(): void
    {
        $values = $this->db->select(['id'])
            ->from('test2');

        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" NOT IN (SELECT "id" FROM "test2")',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->notIn('value', $values))
                ->sql()
        );
    }

    public function testConditionNotLike(): void
    {
        $this->assertSame(
            'SELECT * FROM "test" WHERE "value" NOT LIKE \'test%\'',
            $this->db->select()
                ->from('test')
                ->where(static fn(Query $query): ConditionExpression => $query->expr()
                    ->notLike('value', 'test%'))
                ->sql()
        );
    }
}
