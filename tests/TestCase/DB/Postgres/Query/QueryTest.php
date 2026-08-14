<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Postgres\Query;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Postgres\PostgresConnectionTrait;
use Tests\TestCase\DB\Shared\Query\QueryTestTrait;

final class QueryTest extends TestCase
{
    use ExecuteTestTrait;
    use InsertTestTrait;
    use PostgresConnectionTrait;
    use QueryTestTrait;
    use UpsertTestTrait;
}
