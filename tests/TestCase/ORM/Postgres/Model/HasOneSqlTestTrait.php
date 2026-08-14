<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Postgres\Model;

trait HasOneSqlTestTrait
{
    public function testHasOneContainConditionsSql(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $Users->Addresses->setConditions([
            'Addresses.address_1' => 'test',
        ]);

        $this->assertSame(
            'SELECT "Users"."id" AS "Users__id", "Addresses"."id" AS "Addresses__id" FROM "users" AS "Users" LEFT JOIN "addresses" AS "Addresses" ON "Addresses"."user_id" = "Users"."id" AND "Addresses"."address_1" = \'test\'',
            $Users->find(contain: [
                'Addresses',
            ])
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testHasOneContainJoinTypeSql(): void
    {
        $Users = $this->modelRegistry->use('Users');

        $Users->Addresses->setJoinType('inner');

        $this->assertSame(
            'SELECT "Users"."id" AS "Users__id", "Addresses"."id" AS "Addresses__id" FROM "users" AS "Users" INNER JOIN "addresses" AS "Addresses" ON "Addresses"."user_id" = "Users"."id"',
            $Users->find(contain: [
                'Addresses',
            ])
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testHasOneContainTypeJoinSql(): void
    {
        $this->assertSame(
            'SELECT "Users"."id" AS "Users__id", "Addresses"."id" AS "Addresses__id" FROM "users" AS "Users" INNER JOIN "addresses" AS "Addresses" ON "Addresses"."user_id" = "Users"."id"',
            $this->modelRegistry->use('Users')
                ->find(contain: [
                    'Addresses' => [
                        'type' => 'INNER',
                    ],
                ])
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testHasOneFindSql(): void
    {
        $this->assertSame(
            'SELECT "Users"."id" AS "Users__id", "Addresses"."id" AS "Addresses__id" FROM "users" AS "Users" LEFT JOIN "addresses" AS "Addresses" ON "Addresses"."user_id" = "Users"."id"',
            $this->modelRegistry->use('Users')
                ->find(
                    fields: [
                        'Users.id',
                    ],
                    contain: [
                        'Addresses',
                    ],
                )
                ->sql()
        );
    }

    public function testHasOneInnerJoinSql(): void
    {
        $this->assertSame(
            'SELECT "Users"."id" AS "Users__id" FROM "users" AS "Users" INNER JOIN "addresses" AS "Addresses" ON "Addresses"."user_id" = "Users"."id"',
            $this->modelRegistry->use('Users')
                ->find()
                ->innerJoinWith('Addresses')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testHasOneLeftJoinSql(): void
    {
        $this->assertSame(
            'SELECT "Users"."id" AS "Users__id" FROM "users" AS "Users" LEFT JOIN "addresses" AS "Addresses" ON "Addresses"."user_id" = "Users"."id"',
            $this->modelRegistry->use('Users')
                ->find()
                ->leftJoinWith('Addresses')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testHasOneStrategyFindSql(): void
    {
        $this->assertSame(
            'SELECT "Users"."id" AS "Users__id" FROM "users" AS "Users"',
            $this->modelRegistry->use('Users')
                ->find(
                    fields: [
                        'Users.id',
                    ],
                    contain: [
                        'Addresses' => [
                            'strategy' => 'select',
                        ],
                    ],
                )
                ->sql()
        );
    }
}
