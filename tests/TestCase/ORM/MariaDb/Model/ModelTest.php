<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\MariaDb\Model;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\MariaDb\MariaDbConnectionTrait;
use Tests\TestCase\ORM\Mysql\Model\MysqlModelSqlTestTrait;
use Tests\TestCase\ORM\Shared\Model\ModelTestTrait;

final class ModelTest extends TestCase
{
    use MariaDbConnectionTrait;
    use ModelTestTrait;
    use MysqlModelSqlTestTrait;
}
