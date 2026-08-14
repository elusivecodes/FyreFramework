<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Shared\Model;

use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Query;
use Fyre\ORM\Exceptions\OrmException;

trait JoinTestTrait
{
    public function testContainInnerJoinInvalid(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessage('Model `Posts` does not have a relationship to `Invalid`.');

        $this->modelRegistry->use('Posts')
            ->find()
            ->innerJoinWith('Invalid');
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
                    'conditions' => static fn(Query $query): ConditionExpression => $query->expr()
                        ->equalFields('Users.id', 'Posts.user_id'),
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

    public function testContainLeftJoinInvalid(): void
    {
        $this->expectException(OrmException::class);
        $this->expectExceptionMessage('Model `Posts` does not have a relationship to `Invalid`.');

        $this->modelRegistry->use('Posts')
            ->find()
            ->leftJoinWith('Invalid');
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
                    'conditions' => static fn(Query $query): ConditionExpression => $query->expr()
                        ->equalFields('Users.id', 'Posts.user_id'),
                ],
            ])
            ->disableAutoFields()
            ->sql();
    }
}
