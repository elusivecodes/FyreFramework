<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Shared\ResultSetTestTrait;

final class ResultSetTest extends TestCase
{
    use MysqlConnectionTrait;
    use ResultSetTestTrait;
    use ResultSetTypeTestTrait;
}
