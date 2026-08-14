<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Mysql\Model;

trait MysqlModelSqlTestTrait
{
    use BelongsToSqlTestTrait;
    use ContainSqlTestTrait;
    use HasManySqlTestTrait;
    use HasOneSqlTestTrait;
    use JoinSqlTestTrait;
    use ManyToManySqlTestTrait;
    use MatchingSqlTestTrait;
    use QuerySqlTestTrait;
    use RelationshipSqlTestTrait;
}
