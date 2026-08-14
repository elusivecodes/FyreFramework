<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql\Query;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Mysql\MysqlConnectionTrait;
use Tests\TestCase\DB\Shared\Query\QueryTestTrait;

final class QueryTest extends TestCase
{
    use HintTestTrait;
    use MysqlConnectionTrait;
    use MysqlQueryTestTrait;
    use QueryTestTrait;
}
