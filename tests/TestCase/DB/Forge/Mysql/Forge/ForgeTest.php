<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Mysql\Forge;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Forge\Mysql\MysqlConnectionTrait;

final class ForgeTest extends TestCase
{
    use AddColumnTestTrait;
    use AddForeignKeyTestTrait;
    use AddIndexTestTrait;
    use AlterTableTestTrait;
    use ChangeColumnTestTrait;
    use CreateSchemaTestTrait;
    use CreateTableTestTrait;
    use DropColumnTestTrait;
    use DropForeignKeyTestTrait;
    use DropIndexTestTrait;
    use DropSchemaTestTrait;
    use DropTableTestTrait;
    use MergeQueryTestTrait;
    use MysqlConnectionTrait;
    use RenameColumnTestTrait;
    use RenameTableTestTrait;
}
