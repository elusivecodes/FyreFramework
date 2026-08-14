<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Sqlite\Traits;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\Shared\Traits\SoftDeleteTestTrait;
use Tests\TestCase\ORM\Sqlite\SqliteConnectionTrait;

final class SoftDeleteTest extends TestCase
{
    use SoftDeleteSqlTestTrait;
    use SoftDeleteTestTrait;
    use SqliteConnectionTrait;
}
