<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\MariaDb\Query;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\MariaDb\MariaDbConnectionTrait;

final class QueryTest extends TestCase
{
    use DeleteTestTrait;
    use ExecuteTestTrait;
    use GetTestTrait;
    use InsertTestTrait;
    use MariaDbConnectionTrait;
    use TransactionTestTrait;
    use UpdateTestTrait;
    use UpsertTestTrait;
}
