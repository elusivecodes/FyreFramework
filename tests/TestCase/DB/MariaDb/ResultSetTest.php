<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\MariaDb;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Mysql\ResultSetTypeTestTrait;
use Tests\TestCase\DB\Shared\ResultSetTestTrait;

final class ResultSetTest extends TestCase
{
    use MariaDbConnectionTrait;
    use ResultSetTestTrait;
    use ResultSetTypeTestTrait;
}
