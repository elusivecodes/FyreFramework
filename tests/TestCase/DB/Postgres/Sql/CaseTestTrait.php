<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Postgres\Sql;

use Fyre\DB\Expressions\CaseExpression;
use Fyre\DB\Query;
use InvalidArgumentException;

trait CaseTestTrait
{
    public function testCase(): void
    {
        $this->assertSame(
            'SELECT CASE WHEN "status" = \'active\' THEN \'Enabled\' ELSE \'Disabled\' END AS "status_label" FROM "test"',
            $this->db->select([
                'status_label' => static fn(Query $query): CaseExpression => $query->case()
                    ->when(
                        $query->expr()
                            ->eq('status', 'active'),
                        'Enabled'
                    )
                    ->else('Disabled'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testCaseArrayCondition(): void
    {
        $this->assertSame(
            'SELECT CASE WHEN "status" = \'active\' AND "enabled" = 1 THEN \'Enabled\' ELSE \'Disabled\' END AS "status_label" FROM "test"',
            $this->db->select([
                'status_label' => static fn(Query $query): CaseExpression => $query->case()
                    ->when(
                        [
                            'status' => 'active',
                            'enabled' => true,
                        ],
                        'Enabled'
                    )
                    ->else('Disabled'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testCaseEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Query CASE expression requires at least one WHEN branch.');

        $this->db->select([
            'status_label' => static fn(Query $query): CaseExpression => $query->case(),
        ])
            ->from('test')
            ->sql();
    }

    public function testCaseExpressionResult(): void
    {
        $this->assertSame(
            'SELECT CASE WHEN "test"."name" IS NOT NULL THEN "test"."name" ELSE "test"."fallback_name" END AS "display_name" FROM "test"',
            $this->db->select([
                'display_name' => static fn(Query $query): CaseExpression => $query->case()
                    ->when(
                        $query->expr()
                            ->isNotNull('test.name'),
                        $query->identifier('test.name')
                    )
                    ->else($query->identifier('test.fallback_name')),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testCaseMultiple(): void
    {
        $this->assertSame(
            'SELECT CASE WHEN "status" = \'active\' THEN \'Enabled\' WHEN "status" = \'pending\' THEN \'Pending\' ELSE \'Disabled\' END AS "status_label" FROM "test"',
            $this->db->select([
                'status_label' => static fn(Query $query): CaseExpression => $query->case()
                    ->when(
                        $query->expr()
                            ->eq('status', 'active'),
                        'Enabled'
                    )
                    ->when(
                        $query->expr()
                            ->eq('status', 'pending'),
                        'Pending'
                    )
                    ->else('Disabled'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testCaseNoElse(): void
    {
        $this->assertSame(
            'SELECT CASE WHEN "status" = \'active\' THEN \'Enabled\' END AS "status_label" FROM "test"',
            $this->db->select([
                'status_label' => static fn(Query $query): CaseExpression => $query->case()
                    ->when(
                        $query->expr()
                            ->eq('status', 'active'),
                        'Enabled'
                    ),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testCaseSimple(): void
    {
        $this->assertSame(
            'SELECT CASE "status" WHEN \'active\' THEN \'Enabled\' ELSE \'Disabled\' END AS "status_label" FROM "test"',
            $this->db->select([
                'status_label' => static fn(Query $query): CaseExpression => $query->case($query->identifier('status'))
                    ->when('active', 'Enabled')
                    ->else('Disabled'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testCaseSimpleArrayCondition(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Query simple CASE expression does not support array WHEN values.');

        $this->db->select([
            'status_label' => static fn(Query $query): CaseExpression => $query->case($query->identifier('status'))
                ->when(['active'], 'Enabled'),
        ])
            ->from('test')
            ->sql();
    }
}
