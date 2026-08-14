<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite\Query;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Shared\Query\QueryTestTrait;
use Tests\TestCase\DB\Sqlite\SqliteConnectionTrait;

final class QueryTest extends TestCase
{
    use DeleteTestTrait;
    use ExecuteTestTrait;
    use GetTestTrait;
    use InsertTestTrait;
    use QueryTestTrait;
    use SqliteConnectionTrait;
    use UpsertTestTrait;
}
