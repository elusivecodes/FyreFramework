<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite\Query;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Sqlite\SqliteConnectionTrait;

final class QueryTest extends TestCase
{
    use DeleteTestTrait;
    use ExecuteTestTrait;
    use GetTestTrait;
    use InsertTestTrait;
    use SqliteConnectionTrait;
    use TransactionTestTrait;
    use UpdateTestTrait;
    use UpsertTestTrait;
}
