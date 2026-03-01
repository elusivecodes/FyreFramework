<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Sqlite\Forge;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Forge\Sqlite\SqliteConnectionTrait;

final class ForgeTest extends TestCase
{
    use AddColumnTestTrait;
    use AddIndexTestTrait;
    use CreateTableTestTrait;
    use DropColumnTestTrait;
    use DropIndexTestTrait;
    use DropTableTestTrait;
    use MergeQueryTestTrait;
    use RenameColumnTestTrait;
    use RenameTableTestTrait;
    use SqliteConnectionTrait;
}
