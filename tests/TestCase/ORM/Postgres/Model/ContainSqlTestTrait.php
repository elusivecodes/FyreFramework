<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Postgres\Model;

use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Query;
use Tests\Mock\Entities\User;

trait ContainSqlTestTrait
{
    public function testContainFindSql(): void
    {
        $this->assertSame(
            'SELECT "Posts"."id" AS "Posts__id", "Users"."id" AS "Users__id", "Addresses"."id" AS "Addresses__id" FROM "posts" AS "Posts" LEFT JOIN "users" AS "Users" ON "Users"."id" = "Posts"."user_id" AND "Users"."name" = \'Test\' LEFT JOIN "addresses" AS "Addresses" ON "Addresses"."user_id" = "Users"."id"',
            $this->modelRegistry->use('Posts')
                ->find(contain: [
                    'Users' => [
                        'Addresses',
                        'conditions' => static fn(Query $query): ConditionExpression => $query->expr()
                            ->eq('Users.name', 'Test'),
                    ],
                ])
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testContainOverwrite(): void
    {
        $Posts = $this->modelRegistry->use('Posts');
        $Users = $this->modelRegistry->use('Users');

        $user = $Users->newEntity([
            'name' => 'Test',
        ]);

        $this->assertTrue(
            $Users->save($user)
        );

        $post = $Posts->newEntity([
            'user_id' => $user->id,
            'title' => 'Test',
            'content' => 'This is the content.',
            'comments' => [
                [
                    'user_id' => $user->id,
                    'content' => 'This is a comment',
                ],
            ],
            'tags' => [
                [
                    'tag' => 'test1',
                ],
            ],
        ]);

        $this->assertTrue(
            $Posts->save($post)
        );

        $user = $Users->find(conditions: [
            'Users.id' => 1,
        ])
            ->contain([
                'Posts' => [
                    'Comments',
                ],
            ])
            ->contain([
                'Posts' => [
                    'Tags',
                ],
            ], true)
            ->first();

        $this->assertInstanceOf(
            User::class,
            $user
        );

        $this->assertSame(
            1,
            $user->id
        );

        $this->assertSame(
            1,
            $user->posts[0]->id
        );

        $this->assertNull(
            $user->posts[0]->comments
        );

        $this->assertSame(
            1,
            $user->posts[0]->tags[0]->id
        );
    }
}
