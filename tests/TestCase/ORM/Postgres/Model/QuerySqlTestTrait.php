<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Postgres\Model;

use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Query;

trait QuerySqlTestTrait
{
    public function testFindOptionSql(): void
    {
        $this->assertSame(
            'SELECT "Items"."id" AS "Items__id", CONCAT(Items.name, " ", Items2.name) AS "title" FROM "items" AS "Items" LEFT JOIN "items" AS "Items2" ON "Items2"."id" = "Items"."id" WHERE "Items"."id" = 1 GROUP BY "Items"."id" HAVING "title" = \'Test Test\' ORDER BY "Items"."name" DESC LIMIT 1 FOR UPDATE',
            $this->modelRegistry->use('Items')->find(
                fields: [
                    'title' => 'CONCAT(Items.name, " ", Items2.name)',
                ],
                join: [
                    'Items2' => [
                        'table' => 'items',
                        'type' => 'LEFT',
                        'conditions' => static fn(Query $query): ConditionExpression => $query->expr()
                            ->equalFields('Items2.id', 'Items.id'),
                    ],
                ],
                conditions: static fn(Query $query): ConditionExpression => $query->expr()
                    ->eq('Items.id', 1),
                groupBy: [
                    'Items.id',
                ],
                orderBy: [
                    'Items.name' => 'DESC',
                ],
                having: static fn(Query $query): ConditionExpression => $query->expr()
                    ->eq('title', 'Test Test'),
                limit: 1,
                offset: 0,
                epilog: 'FOR UPDATE',
            )->sql()
        );
    }

    public function testFindSubquery(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $this->assertSame(
            'SELECT "Items"."id" AS "Items__id", (SELECT "Users"."name" AS "user_name" FROM "users" AS "Users" INNER JOIN "posts" AS "Posts" ON "Posts"."user_id" = "Users"."id" WHERE "Users"."id" = "Items"."id" LIMIT 1) AS "user_name" FROM "items" AS "Items"',
            $Items->find(fields: [
                'user_name' => $this->modelRegistry->use('Users')
                    ->subquery(
                        conditions: static fn(Query $query): ConditionExpression => $query->expr()
                            ->equalFields('Users.id', 'Items.id')
                    )
                    ->select([
                        'user_name' => 'Users.name',
                    ])
                    ->innerJoinWith('Posts')
                    ->limit(1),
            ])->sql()
        );
    }

    public function testFindSubqueryAlias(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $this->assertSame(
            'SELECT "Items"."id" AS "Items__id", (SELECT "Alias"."name" AS "user_name" FROM "users" AS "Alias" INNER JOIN "posts" AS "Posts" ON "Posts"."user_id" = "Alias"."id" WHERE Alias.id = Items.id LIMIT 1) AS "user_name" FROM "items" AS "Items"',
            $Items->find(fields: [
                'user_name' => $this->modelRegistry->use('Users')
                    ->subquery(alias: 'Alias')
                    ->select([
                        'user_name' => 'Alias.name',
                    ])
                    ->innerJoinWith('Posts')
                    ->where([
                        'Alias.id = Items.id',
                    ])
                    ->limit(1),
            ])->sql()
        );
    }
}
