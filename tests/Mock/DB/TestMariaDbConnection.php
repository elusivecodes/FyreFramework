<?php
declare(strict_types=1);

namespace Tests\Mock\DB;

use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Override;
use Tests\Mock\DB\Traits\TestConnectionTrait;

class TestMariaDbConnection extends MysqlConnection
{
    use TestConnectionTrait;

    #[Override]
    public function version(): string
    {
        return '11.0-MariaDB';
    }
}
