<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Postgres\Forge;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Forge\Postgres\PostgresConnectionTrait;

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
    use PostgresConnectionTrait;
    use RenameColumnTestTrait;
    use RenameTableTestTrait;
}
