<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Postgres\Model;

trait ManyToManySqlTestTrait
{
    public function testManyToManyInnerJoinSql(): void
    {
        $this->assertSame(
            'SELECT "Posts"."id" AS "Posts__id" FROM "posts" AS "Posts" INNER JOIN "posts_tags" AS "PostsTags" ON "PostsTags"."post_id" = "Posts"."id" INNER JOIN "tags" AS "Tags" ON "Tags"."id" = "PostsTags"."tag_id"',
            $this->modelRegistry->use('Posts')
                ->find()
                ->innerJoinWith('Tags')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testManyToManyLeftJoinSql(): void
    {
        $this->assertSame(
            'SELECT "Posts"."id" AS "Posts__id" FROM "posts" AS "Posts" LEFT JOIN "posts_tags" AS "PostsTags" ON "PostsTags"."post_id" = "Posts"."id" LEFT JOIN "tags" AS "Tags" ON "Tags"."id" = "PostsTags"."tag_id"',
            $this->modelRegistry->use('Posts')
                ->find()
                ->leftJoinWith('Tags')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testManyToManySelfSql(): void
    {
        $Items = $this->modelRegistry->use('Items');

        $Items->manyToMany('ChildItems', [
            'classAlias' => 'Items',
            'through' => 'Contains',
            'foreignKey' => 'item_id',
            'targetForeignKey' => 'contained_item_id',
        ]);

        $Items->manyToMany('ParentItems', [
            'classAlias' => 'Items',
            'through' => 'Contains',
            'foreignKey' => 'contained_item_id',
            'targetForeignKey' => 'item_id',
        ]);

        $this->assertSame(
            'SELECT "Items"."id" AS "Items__id" FROM "items" AS "Items" INNER JOIN "contains" AS "Contains" ON "Contains"."item_id" = "Items"."id" INNER JOIN "items" AS "ChildItems" ON "ChildItems"."id" = "Contains"."contained_item_id"',
            $Items->find()
                ->innerJoinWith('ChildItems')
                ->disableAutoFields()
                ->sql()
        );

        $this->assertSame(
            'SELECT "Items"."id" AS "Items__id" FROM "items" AS "Items" INNER JOIN "contains" AS "Contains" ON "Contains"."contained_item_id" = "Items"."id" INNER JOIN "items" AS "ParentItems" ON "ParentItems"."id" = "Contains"."item_id"',
            $Items->find()
                ->innerJoinWith('ParentItems')
                ->disableAutoFields()
                ->sql()
        );
    }
}
