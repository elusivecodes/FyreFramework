<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql\Sql;

trait MysqlSqlTestTrait
{
    use AggregateTestTrait;
    use CaseTestTrait;
    use ConditionTestTrait;
    use DeleteTestTrait;
    use ExceptTestTrait;
    use FunctionTestTrait;
    use GroupLimitTestTrait;
    use HavingTestTrait;
    use InsertFromTestTrait;
    use InsertTestTrait;
    use IntersectTestTrait;
    use JoinTestTrait;
    use SelectTestTrait;
    use UnionAllTestTrait;
    use UnionTestTrait;
    use UpdateBatchTestTrait;
    use UpdateTestTrait;
    use UpsertTestTrait;
    use WhereTestTrait;
    use WindowTestTrait;
    use WithTestTrait;
}
