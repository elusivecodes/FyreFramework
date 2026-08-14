<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Mysql;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\Shared\QueryTestTrait;

final class QueryTest extends TestCase
{
    use MysqlConnectionTrait;
    use QueryTestTrait;
}
