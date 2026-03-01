<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Sqlite\Model;

use Fyre\ORM\Exceptions\OrmException;

trait JoinTestTrait
{
    public function testContainInnerJoinConditionsSql(): void
    {
        $this->assertSame(
            'SELECT Posts.id AS Posts__id FROM posts AS Posts INNER JOIN users AS Users ON Users.id = Posts.user_id INNER JOIN addresses AS Addresses ON Addresses.user_id = Users.id AND Addresses.suburb = \'Test\'',
            $this->modelRegistry->use('Posts')
                ->find()
                ->innerJoinWith('Users.Addresses', [
                    'Addresses.suburb' => 'Test',
                ])
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testContainInnerJoinInvalid(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessage('Model `Posts` does not have a relationship to `Invalid`.');

        $this->modelRegistry->use('Posts')
            ->find()
            ->innerJoinWith('Invalid');
    }

    public function testContainInnerJoinMerge(): void
    {
        $this->assertSame(
            'SELECT Posts.id AS Posts__id FROM posts AS Posts INNER JOIN users AS Users ON Users.id = Posts.user_id INNER JOIN addresses AS Addresses ON Addresses.user_id = Users.id',
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
            'SELECT Posts.id AS Posts__id FROM posts AS Posts INNER JOIN users AS Users ON Users.id = Posts.user_id',
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
            'SELECT Posts.id AS Posts__id FROM posts AS Posts INNER JOIN users AS Users ON Users.id = Posts.user_id INNER JOIN addresses AS Addresses ON Addresses.user_id = Users.id',
            $this->modelRegistry->use('Posts')
                ->find()
                ->innerJoinWith('Users.Addresses')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testContainJoinConflict(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessage('Join table alias `Users` is already used by the query.');

        $this->modelRegistry->use('Posts')
            ->find()
            ->join([
                'Users' => [
                    'table' => 'users',
                    'conditions' => [
                        'Users.id = Posts.user_id',
                    ],
                ],
            ])
            ->innerJoinWith('Users')
            ->disableAutoFields()
            ->sql();
    }

    public function testContainJoinContainConflict(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessage('Join table alias `Users` is already used by the query.');

        $this->modelRegistry->use('Posts')
            ->find()
            ->contain([
                'Users' => [
                    'autoFields' => false,
                ],
            ])
            ->innerJoinWith('Comments.Users')
            ->disableAutoFields()
            ->sql();
    }

    public function testContainJoinInvalidOptions(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessage('Contain option `orderBy` cannot be used with the join strategy.');

        $this->modelRegistry->use('Posts')
            ->find()
            ->contain([
                'Users' => [
                    'autoFields' => false,
                    'orderBy' => [
                        'Posts.id' => 'ASC',
                    ],
                ],
            ])
            ->disableAutoFields()
            ->sql();
    }

    public function testContainJoinPathConflict(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessage('Join table alias `Users` is already used by the query.');

        $this->modelRegistry->use('Posts')
            ->find()
            ->innerJoinWith('Users')
            ->innerJoinWith('Comments.Users')
            ->disableAutoFields()
            ->sql();
    }

    public function testContainJoinPathMerge(): void
    {
        $this->assertSame(
            'SELECT Posts.id AS Posts__id, Users.id AS Users__id FROM posts AS Posts INNER JOIN users AS Users ON Users.id = Posts.user_id',
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
            'SELECT Posts.id AS Posts__id, Users.id AS Users__id FROM posts AS Posts INNER JOIN users AS Users ON Users.id = Posts.user_id',
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
            'SELECT Posts.id AS Posts__id FROM posts AS Posts LEFT JOIN users AS Users ON Users.id = Posts.user_id LEFT JOIN addresses AS Addresses ON Addresses.user_id = Users.id AND Addresses.suburb = \'Test\'',
            $this->modelRegistry->use('Posts')
                ->find()
                ->leftJoinWith('Users.Addresses', [
                    'Addresses.suburb' => 'Test',
                ])
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testContainLeftJoinInvalid(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessage('Model `Posts` does not have a relationship to `Invalid`.');

        $this->modelRegistry->use('Posts')
            ->find()
            ->leftJoinWith('Invalid');
    }

    public function testContainLeftJoinMerge(): void
    {
        $this->assertSame(
            'SELECT Posts.id AS Posts__id FROM posts AS Posts INNER JOIN users AS Users ON Users.id = Posts.user_id LEFT JOIN addresses AS Addresses ON Addresses.user_id = Users.id',
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
            'SELECT Posts.id AS Posts__id FROM posts AS Posts LEFT JOIN users AS Users ON Users.id = Posts.user_id',
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
            'SELECT Posts.id AS Posts__id FROM posts AS Posts LEFT JOIN users AS Users ON Users.id = Posts.user_id LEFT JOIN addresses AS Addresses ON Addresses.user_id = Users.id',
            $this->modelRegistry->use('Posts')
                ->find()
                ->leftJoinWith('Users.Addresses')
                ->disableAutoFields()
                ->sql()
        );
    }

    public function testJoinConflict(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessage('Join table alias `Users` is already used by the query.');

        $this->modelRegistry->use('Posts')
            ->find()
            ->innerJoinWith('Users')
            ->join([
                'Users' => [
                    'table' => 'users',
                    'conditions' => [
                        'Users.id = Posts.user_id',
                    ],
                ],
            ])
            ->disableAutoFields()
            ->sql();
    }

    public function testJoinContainJoinOrder(): void
    {
        $this->assertSame(
            'SELECT Posts.id AS Posts__id, Users.id AS Users__id FROM posts AS Posts INNER JOIN comments AS Comments ON Comments.post_id = Posts.id INNER JOIN users AS CommentsUsers ON CommentsUsers.id = Comments.user_id INNER JOIN users AS Users ON Users.id = Posts.user_id',
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
                        'conditions' => [
                            'CommentsUsers.id = Comments.user_id',
                        ],
                    ],
                ])
                ->disableAutoFields()
                ->sql()
        );
    }
}
