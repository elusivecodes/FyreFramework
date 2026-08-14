<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Mysql\Model;

use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Query;

trait JoinSqlTestTrait
{
    public function testContainInnerJoinConditionsSql(): void
    {
        $this->assertSame(
            'SELECT `Posts`.`id` AS `Posts__id` FROM `posts` AS `Posts` INNER JOIN `users` AS `Users` ON `Users`.`id` = `Posts`.`user_id` INNER JOIN `addresses` AS `Addresses` ON `Addresses`.`user_id` = `Users`.`id` AND `Addresses`.`suburb` = \'Test\'',
            $this->modelRegistry->use('Posts')
                ->find()
                ->innerJoinWith(
                    'Users.Addresses',
                    static fn(Query $query): ConditionExpression => $query->expr()
                        ->eq('Addresses.suburb', 'Test')
                )
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testContainInnerJoinMerge(): void
    {
        $this->assertSame(
            'SELECT `Posts`.`id` AS `Posts__id` FROM `posts` AS `Posts` INNER JOIN `users` AS `Users` ON `Users`.`id` = `Posts`.`user_id` INNER JOIN `addresses` AS `Addresses` ON `Addresses`.`user_id` = `Users`.`id`',
            $this->modelRegistry->use('Posts')
                ->find()
                ->leftJoinWith('Users')
                ->innerJoinWith('Users.Addresses')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testContainInnerJoinOverwrite(): void
    {
        $this->assertSame(
            'SELECT `Posts`.`id` AS `Posts__id` FROM `posts` AS `Posts` INNER JOIN `users` AS `Users` ON `Users`.`id` = `Posts`.`user_id`',
            $this->modelRegistry->use('Posts')
                ->find()
                ->leftJoinWith('Users')
                ->innerJoinWith('Users')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testContainInnerJoinSql(): void
    {
        $this->assertSame(
            'SELECT `Posts`.`id` AS `Posts__id` FROM `posts` AS `Posts` INNER JOIN `users` AS `Users` ON `Users`.`id` = `Posts`.`user_id` INNER JOIN `addresses` AS `Addresses` ON `Addresses`.`user_id` = `Users`.`id`',
            $this->modelRegistry->use('Posts')
                ->find()
                ->innerJoinWith('Users.Addresses')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testContainJoinPathMerge(): void
    {
        $this->assertSame(
            'SELECT `Posts`.`id` AS `Posts__id`, `Users`.`id` AS `Users__id` FROM `posts` AS `Posts` INNER JOIN `users` AS `Users` ON `Users`.`id` = `Posts`.`user_id`',
            $this->modelRegistry->use('Posts')
                ->find()
                ->contain([
                    'Users' => [
                        'autoFields' => false,
                    ],
                ])
                ->innerJoinWith('Users')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testContainJoinType(): void
    {
        $this->assertSame(
            'SELECT `Posts`.`id` AS `Posts__id`, `Users`.`id` AS `Users__id` FROM `posts` AS `Posts` INNER JOIN `users` AS `Users` ON `Users`.`id` = `Posts`.`user_id`',
            $this->modelRegistry->use('Posts')
                ->find()
                ->contain([
                    'Users' => [
                        'autoFields' => false,
                        'type' => 'INNER',
                    ],
                ])
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testContainLeftJoinConditionsSql(): void
    {
        $this->assertSame(
            'SELECT `Posts`.`id` AS `Posts__id` FROM `posts` AS `Posts` LEFT JOIN `users` AS `Users` ON `Users`.`id` = `Posts`.`user_id` LEFT JOIN `addresses` AS `Addresses` ON `Addresses`.`user_id` = `Users`.`id` AND `Addresses`.`suburb` = \'Test\'',
            $this->modelRegistry->use('Posts')
                ->find()
                ->leftJoinWith(
                    'Users.Addresses',
                    static fn(Query $query): ConditionExpression => $query->expr()
                        ->eq('Addresses.suburb', 'Test')
                )
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testContainLeftJoinMerge(): void
    {
        $this->assertSame(
            'SELECT `Posts`.`id` AS `Posts__id` FROM `posts` AS `Posts` INNER JOIN `users` AS `Users` ON `Users`.`id` = `Posts`.`user_id` LEFT JOIN `addresses` AS `Addresses` ON `Addresses`.`user_id` = `Users`.`id`',
            $this->modelRegistry->use('Posts')
                ->find()
                ->innerJoinWith('Users')
                ->leftJoinWith('Users.Addresses')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testContainLeftJoinOverwrite(): void
    {
        $this->assertSame(
            'SELECT `Posts`.`id` AS `Posts__id` FROM `posts` AS `Posts` LEFT JOIN `users` AS `Users` ON `Users`.`id` = `Posts`.`user_id`',
            $this->modelRegistry->use('Posts')
                ->find()
                ->innerJoinWith('Users')
                ->leftJoinWith('Users')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testContainLeftJoinSql(): void
    {
        $this->assertSame(
            'SELECT `Posts`.`id` AS `Posts__id` FROM `posts` AS `Posts` LEFT JOIN `users` AS `Users` ON `Users`.`id` = `Posts`.`user_id` LEFT JOIN `addresses` AS `Addresses` ON `Addresses`.`user_id` = `Users`.`id`',
            $this->modelRegistry->use('Posts')
                ->find()
                ->leftJoinWith('Users.Addresses')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testJoinContainJoinOrder(): void
    {
        $this->assertSame(
            'SELECT `Posts`.`id` AS `Posts__id`, `Users`.`id` AS `Users__id` FROM `posts` AS `Posts` INNER JOIN `comments` AS `Comments` ON `Comments`.`post_id` = `Posts`.`id` INNER JOIN `users` AS `CommentsUsers` ON `CommentsUsers`.`id` = `Comments`.`user_id` INNER JOIN `users` AS `Users` ON `Users`.`id` = `Posts`.`user_id`',
            $this->modelRegistry->use('Posts')
                ->find()
                ->contain([
                    'Users' => [
                        'autoFields' => false,
                        'type' => 'INNER',
                    ],
                ])
                ->innerJoinWith('Comments')
                ->join([
                    'CommentsUsers' => [
                        'table' => 'users',
                        'conditions' => static fn(Query $query): ConditionExpression => $query->expr()
                            ->equalFields('CommentsUsers.id', 'Comments.user_id'),
                    ],
                ])
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testJoinOverwriteClearsJoinPaths(): void
    {
        $this->assertSame(
            'SELECT `Posts`.`id` AS `Posts__id` FROM `posts` AS `Posts` INNER JOIN `comments` AS `Comments` ON `Comments`.`post_id` = `Posts`.`id` INNER JOIN `users` AS `Users` ON `Users`.`id` = `Posts`.`user_id`',
            $this->modelRegistry->use('Posts')
                ->find()
                ->innerJoinWith('Users')
                ->join([
                    'Comments' => [
                        'table' => 'comments',
                        'conditions' => static fn(Query $query): ConditionExpression => $query->expr()
                            ->equalFields('Comments.post_id', 'Posts.id'),
                    ],
                ], true)
                ->join([
                    'Users' => [
                        'table' => 'users',
                        'conditions' => static fn(Query $query): ConditionExpression => $query->expr()
                            ->equalFields('Users.id', 'Posts.user_id'),
                    ],
                ])
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testJoinOverwriteClearsMatching(): void
    {
        $this->assertSame(
            'SELECT `Posts`.`id` AS `Posts__id` FROM `posts` AS `Posts` INNER JOIN `comments` AS `Comments` ON `Comments`.`post_id` = `Posts`.`id`',
            $this->modelRegistry->use('Posts')
                ->find()
                ->matching('Users')
                ->join([
                    'Comments' => [
                        'table' => 'comments',
                        'conditions' => static fn(Query $query): ConditionExpression => $query->expr()
                            ->equalFields('Comments.post_id', 'Posts.id'),
                    ],
                ], true)
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testJoinResetRestoresJoinPaths(): void
    {
        $query = $this->modelRegistry->use('Posts')
            ->find()
            ->contain([
                'Users' => [
                    'autoFields' => false,
                ],
            ])
            ->disableAutoFields();

        $query->sql(reset: false);
        $query->reset();

        $this->assertSame(
            'SELECT `Posts`.`id` AS `Posts__id` FROM `posts` AS `Posts` INNER JOIN `users` AS `Users` ON `Users`.`id` = `Posts`.`user_id`',
            $query
                ->contain([], true)
                ->join([
                    'Users' => [
                        'table' => 'users',
                        'conditions' => static fn(Query $query): ConditionExpression => $query->expr()
                            ->equalFields('Users.id', 'Posts.user_id'),
                    ],
                ])
                ->sql()
        );
    }
}
