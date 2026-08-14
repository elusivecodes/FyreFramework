<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Mysql\Model;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\Mysql\MysqlConnectionTrait;
use Tests\TestCase\ORM\Shared\Model\ModelTestTrait;

final class ModelTest extends TestCase
{
    use ModelTestTrait;
    use MysqlConnectionTrait;
    use MysqlModelSqlTestTrait;
}
