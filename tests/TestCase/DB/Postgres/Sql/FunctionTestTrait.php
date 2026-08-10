<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Postgres\Sql;

use Fyre\DB\Expressions\FunctionExpression;
use Fyre\DB\Query;

trait FunctionTestTrait
{
    public function testFunctionAbs(): void
    {
        $this->assertSame(
            'SELECT ABS("test"."value") AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->abs('test.value'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionCast(): void
    {
        $this->assertSame(
            'SELECT CAST("test"."value" AS CHAR) AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->cast('test.value', 'CHAR'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionCeil(): void
    {
        $this->assertSame(
            'SELECT CEIL("test"."value") AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->ceil('test.value'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionCoalesce(): void
    {
        $this->assertSame(
            'SELECT COALESCE("test"."value", \'fallback\') AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->coalesce([
                        $query->identifier('test.value'),
                        'fallback',
                    ]),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionConcat(): void
    {
        $this->assertSame(
            'SELECT CONCAT("test"."first", \' \', "test"."last") AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->concat([
                        $query->identifier('test.first'),
                        ' ',
                        $query->identifier('test.last'),
                    ]),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionDateAddWeek(): void
    {
        $this->assertSame(
            'SELECT ("test"."created" + (1 * INTERVAL \'1 week\')) AS "date" FROM "test"',
            $this->db->select([
                'date' => static fn(Query $query): FunctionExpression => $query->func()
                    ->dateAdd('test.created', 1, 'week'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionDateDiff(): void
    {
        $this->assertSame(
            'SELECT DATE_PART(\'day\', "test"."created" - "test"."updated") AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->dateDiff([
                        $query->identifier('test.created'),
                        $query->identifier('test.updated'),
                    ]),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionDatePart(): void
    {
        $this->assertSame(
            'SELECT DATE_PART(\'year\', "test"."created") AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->datePart('year', 'test.created'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionDateSubWeek(): void
    {
        $this->assertSame(
            'SELECT ("test"."created" - (1 * INTERVAL \'1 week\')) AS "date" FROM "test"',
            $this->db->select([
                'date' => static fn(Query $query): FunctionExpression => $query->func()
                    ->dateSub('test.created', 1, 'week'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionDayOfWeek(): void
    {
        $this->assertSame(
            'SELECT (EXTRACT(DOW FROM "test"."created") + 1) AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->dayOfWeek('test.created'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionExtract(): void
    {
        $this->assertSame(
            'SELECT EXTRACT(MONTH FROM "test"."created") AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->extract('month', 'test.created'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionFloor(): void
    {
        $this->assertSame(
            'SELECT FLOOR("test"."value") AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->floor('test.value'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionJsonValue(): void
    {
        $this->assertSame(
            'SELECT (JSONB_PATH_QUERY_FIRST("test"."data"::jsonb, \'$.name\') #>> \'{}\') AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->jsonValue('test.data', '$.name'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionLength(): void
    {
        $this->assertSame(
            'SELECT LENGTH("test"."value") AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->length('test.value'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionLower(): void
    {
        $this->assertSame(
            'SELECT LOWER("test"."value") AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->lower('test.value'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionNowDate(): void
    {
        $this->assertSame(
            'SELECT CURRENT_DATE AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->now('date'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionNowDateTime(): void
    {
        $this->assertSame(
            'SELECT CURRENT_TIMESTAMP AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->now(),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionNowTime(): void
    {
        $this->assertSame(
            'SELECT CURRENT_TIME AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->now('time'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionNullIf(): void
    {
        $this->assertSame(
            'SELECT NULLIF("test"."value", 0) AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->nullIf('test.value', 0),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionReplace(): void
    {
        $this->assertSame(
            'SELECT REPLACE("test"."value", \'old\', \'new\') AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->replace('test.value', 'old', 'new'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionRound(): void
    {
        $this->assertSame(
            'SELECT ROUND("test"."value", 0) AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->round('test.value'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionRoundPrecision(): void
    {
        $this->assertSame(
            'SELECT ROUND("test"."value", 2) AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->round('test.value', 2),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionSubstring(): void
    {
        $this->assertSame(
            'SELECT SUBSTRING("test"."value", 2, 3) AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->substring('test.value', 2, 3),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionSubstringWithoutLength(): void
    {
        $this->assertSame(
            'SELECT SUBSTRING("test"."value", 2) AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->substring('test.value', 2),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionTrim(): void
    {
        $this->assertSame(
            'SELECT TRIM("test"."value") AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->trim('test.value'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionUpper(): void
    {
        $this->assertSame(
            'SELECT UPPER("test"."value") AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->upper('test.value'),
            ])
                ->from('test')
                ->sql()
        );
    }

    public function testFunctionWeekDay(): void
    {
        $this->assertSame(
            'SELECT (EXTRACT(ISODOW FROM "test"."created") - 1) AS "value" FROM "test"',
            $this->db->select([
                'value' => static fn(Query $query): FunctionExpression => $query->func()
                    ->weekDay('test.created'),
            ])
                ->from('test')
                ->sql()
        );
    }
}
