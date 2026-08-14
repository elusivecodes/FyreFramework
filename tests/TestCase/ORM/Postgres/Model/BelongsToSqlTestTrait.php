<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Postgres\Model;

trait BelongsToSqlTestTrait
{
    public function testBelongsToContainConditionsSql(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $Addresses->Users->setConditions([
            'Users.name' => 'test',
        ]);

        $this->assertSame(
            'SELECT "Addresses"."id" AS "Addresses__id", "Users"."id" AS "Users__id" FROM "addresses" AS "Addresses" LEFT JOIN "users" AS "Users" ON "Users"."id" = "Addresses"."user_id" AND "Users"."name" = \'test\'',
            $Addresses->find(contain: [
                'Users',
            ])
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testBelongsToContainJoinTypeSql(): void
    {
        $Addresses = $this->modelRegistry->use('Addresses');

        $Addresses->Users->setJoinType('inner');

        $this->assertSame(
            'SELECT "Addresses"."id" AS "Addresses__id", "Users"."id" AS "Users__id" FROM "addresses" AS "Addresses" INNER JOIN "users" AS "Users" ON "Users"."id" = "Addresses"."user_id"',
            $Addresses->find(contain: [
                'Users',
            ])
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testBelongsToContainTypeJoinSql(): void
    {
        $this->assertSame(
            'SELECT "Addresses"."id" AS "Addresses__id", "Users"."id" AS "Users__id" FROM "addresses" AS "Addresses" INNER JOIN "users" AS "Users" ON "Users"."id" = "Addresses"."user_id"',
            $this->modelRegistry->use('Addresses')
                ->find(contain: [
                    'Users' => [
                        'type' => 'INNER',
                    ],
                ])
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testBelongsToFindSql(): void
    {
        $this->assertSame(
            'SELECT "Addresses"."id" AS "Addresses__id", "Users"."id" AS "Users__id" FROM "addresses" AS "Addresses" LEFT JOIN "users" AS "Users" ON "Users"."id" = "Addresses"."user_id"',
            $this->modelRegistry->use('Addresses')
                ->find(contain: [
                    'Users',
                ])
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testBelongsToInnerJoinSql(): void
    {
        $this->assertSame(
            'SELECT "Addresses"."id" AS "Addresses__id" FROM "addresses" AS "Addresses" INNER JOIN "users" AS "Users" ON "Users"."id" = "Addresses"."user_id"',
            $this->modelRegistry->use('Addresses')
                ->find()
                ->innerJoinWith('Users')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testBelongsToLeftJoinSql(): void
    {
        $this->assertSame(
            'SELECT "Addresses"."id" AS "Addresses__id" FROM "addresses" AS "Addresses" LEFT JOIN "users" AS "Users" ON "Users"."id" = "Addresses"."user_id"',
            $this->modelRegistry->use('Addresses')
                ->find()
                ->leftJoinWith('Users')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testBelongsToStrategyFindSql(): void
    {
        $this->assertSame(
            'SELECT "Addresses"."id" AS "Addresses__id", "Addresses"."user_id" AS "Addresses__user_id" FROM "addresses" AS "Addresses"',
            $this->modelRegistry->use('Addresses')
                ->find(contain: [
                    'Users' => [
                        'strategy' => 'select',
                    ],
                ])
                ->disableAutoFields()
                ->sql()
        );
    }
}
