<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Shared\ResultSetTestTrait;

final class ResultSetTest extends TestCase
{
    use ResultSetTestTrait;
    use ResultSetTypeTestTrait;
    use SqliteConnectionTrait;
}
