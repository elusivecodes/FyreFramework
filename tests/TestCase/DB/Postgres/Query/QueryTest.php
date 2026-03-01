<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Postgres\Query;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Postgres\PostgresConnectionTrait;

final class QueryTest extends TestCase
{
    use DeleteTestTrait;
    use ExecuteTestTrait;
    use GetTestTrait;
    use InsertTestTrait;
    use PostgresConnectionTrait;
    use TransactionTestTrait;
    use UpdateTestTrait;
    use UpsertTestTrait;
}
