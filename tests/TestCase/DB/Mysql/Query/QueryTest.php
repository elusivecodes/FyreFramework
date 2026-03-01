<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql\Query;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Mysql\MysqlConnectionTrait;

final class QueryTest extends TestCase
{
    use DeleteTestTrait;
    use ExecuteTestTrait;
    use GetTestTrait;
    use InsertTestTrait;
    use MysqlConnectionTrait;
    use TransactionTestTrait;
    use UpdateTestTrait;
    use UpsertTestTrait;
}
