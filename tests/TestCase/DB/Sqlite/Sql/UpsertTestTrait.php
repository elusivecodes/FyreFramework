<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite\Sql;

use Fyre\DB\Expressions\LiteralExpression;
use Fyre\DB\Queries\SelectQuery;
use Fyre\DB\Query;

trait UpsertTestTrait
{
    public function testUpsert(): void
    {
        $this->assertSame(
            'INSERT INTO "test" ("id", "name", "value") VALUES (1, \'Test 1\', 1), (2, \'Test 2\', 2) ON CONFLICT ("id") DO UPDATE SET "name" = excluded."name", "value" = excluded."value"',
            $this->db->upsert(['id'])
                ->into('test')
                ->values([
                    [
                        'id' => 1,
                        'name' => 'Test 1',
                        'value' => 1,
                    ],
                    [
                        'id' => 2,
                        'name' => 'Test 2',
                        'value' => 2,
                    ],
                ])
                ->sql()
        );
    }

    public function testUpsertClosure(): void
    {
        $query = $this->db->select(['id'])
            ->from('test')
            ->limit(1);

        $this->assertSame(
            'INSERT INTO "test" ("name", "value") VALUES (\'Test 1\', (SELECT "id" FROM "test" LIMIT 1)), (\'Test 2\', (SELECT "id" FROM "test" LIMIT 1)) ON CONFLICT ("value") DO UPDATE SET "name" = excluded."name"',
            $this->db->upsert(['value'])
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

    public function testUpsertColumnOrder(): void
    {
        $this->assertSame(
            'INSERT INTO "test" ("id", "name", "value") VALUES (1, \'Test 1\', 1), (2, \'Test 2\', 2) ON CONFLICT ("id") DO UPDATE SET "name" = excluded."name", "value" = excluded."value"',
            $this->db->upsert(['id'])
                ->into('test')
                ->values([
                    [
                        'id' => 1,
                        'name' => 'Test 1',
                        'value' => 1,
                    ],
                    [
                        'value' => 2,
                        'name' => 'Test 2',
                        'id' => 2,
                    ],
                ])
                ->sql()
        );
    }

    public function testUpsertExcludeUpdate(): void
    {
        $this->assertSame(
            'INSERT INTO "test" ("id", "name", "value") VALUES (1, \'Test 1\', 1), (2, \'Test 2\', 2) ON CONFLICT ("id") DO UPDATE SET "name" = excluded."name"',
            $this->db->upsert(['id'])
                ->into('test')
                ->values([
                    [
                        'id' => 1,
                        'name' => 'Test 1',
                        'value' => 1,
                    ],
                    [
                        'id' => 2,
                        'name' => 'Test 2',
                        'value' => 2,
                    ],
                ], [
                    'value',
                ])
                ->sql()
        );
    }

    public function testUpsertLiteral(): void
    {
        $this->assertSame(
            'INSERT INTO "test" ("name", "value") VALUES (\'Test 1\', 2 * 10), (\'Test 2\', 2 * 20) ON CONFLICT ("value") DO UPDATE SET "name" = excluded."name"',
            $this->db->upsert(['value'])
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

    public function testUpsertMerge(): void
    {
        $this->assertSame(
            'INSERT INTO "test" ("id", "name", "value") VALUES (1, \'Test 1\', 1), (2, \'Test 2\', 2) ON CONFLICT ("id") DO UPDATE SET "name" = excluded."name", "value" = excluded."value"',
            $this->db->upsert(['id'])
                ->into('test')
                ->values([
                    [
                        'id' => 1,
                        'name' => 'Test 1',
                        'value' => 1,
                    ],
                ])
                ->values([
                    [
                        'id' => 2,
                        'name' => 'Test 2',
                        'value' => 2,
                    ],
                ])
                ->sql()
        );
    }

    public function testUpsertOverwrite(): void
    {
        $this->assertSame(
            'INSERT INTO "test" ("id", "name", "value") VALUES (2, \'Test 2\', 2) ON CONFLICT ("id") DO UPDATE SET "name" = excluded."name", "value" = excluded."value"',
            $this->db->upsert(['id'])
                ->into('test')
                ->values([
                    [
                        'id' => 1,
                        'name' => 'Test 1',
                        'value' => 1,
                    ],
                ])
                ->values([
                    [
                        'id' => 2,
                        'name' => 'Test 2',
                        'value' => 2,
                    ],
                ], overwrite: true)
                ->sql()
        );
    }

    public function testUpsertSelectQuery(): void
    {
        $this->assertSame(
            'INSERT INTO "test" ("name", "value") VALUES (\'Test 1\', (SELECT "id" FROM "test" LIMIT 1)), (\'Test 2\', (SELECT "id" FROM "test" LIMIT 1)) ON CONFLICT ("id") DO UPDATE SET "name" = excluded."name", "value" = excluded."value"',
            $this->db->upsert(['id'])
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
