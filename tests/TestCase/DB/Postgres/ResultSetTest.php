<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Postgres;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Shared\ResultSetTestTrait;

final class ResultSetTest extends TestCase
{
    use PostgresConnectionTrait;
    use ResultSetTestTrait;
    use ResultSetTypeTestTrait;
}
