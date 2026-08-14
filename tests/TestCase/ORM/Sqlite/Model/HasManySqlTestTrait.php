<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Sqlite\Model;

trait HasManySqlTestTrait
{
    public function testHasManyInnerJoinSql(): void
    {
        $this->assertSame(
            'SELECT "Users"."id" AS "Users__id" FROM "users" AS "Users" INNER JOIN "posts" AS "Posts" ON "Posts"."user_id" = "Users"."id"',
            $this->modelRegistry->use('Users')
                ->find()
                ->innerJoinWith('Posts')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testHasManyLeftJoinSql(): void
    {
        $this->assertSame(
            'SELECT "Users"."id" AS "Users__id" FROM "users" AS "Users" LEFT JOIN "posts" AS "Posts" ON "Posts"."user_id" = "Users"."id"',
            $this->modelRegistry->use('Users')
                ->find()
                ->leftJoinWith('Posts')
                ->disableAutoFields()
                ->sql()
        );
    }
}
