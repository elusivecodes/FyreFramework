<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql\Query;

trait MysqlQueryTestTrait
{
    use ExecuteTestTrait;
    use GetTestTrait;
    use InsertTestTrait;
    use UpsertTestTrait;
}
