<?php
declare(strict_types=1);

namespace Tests\Mock\DB;

use Fyre\DB\Handlers\Postgres\PostgresConnection;
use Tests\Mock\DB\Traits\TestConnectionTrait;

class TestPostgresConnection extends PostgresConnection
{
    use TestConnectionTrait;
}
