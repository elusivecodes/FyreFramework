<?php
declare(strict_types=1);

namespace Tests\Mock\DB;

use Fyre\DB\Handlers\Mysql\MysqlConnection;
use Override;
use Tests\Mock\DB\Traits\TestConnectionTrait;

class TestMysqlConnection extends MysqlConnection
{
    use TestConnectionTrait;

    #[Override]
    public function version(): string
    {
        return '8.0';
    }
}
