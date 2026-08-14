<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Mysql\Traits;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\Mysql\MysqlConnectionTrait;
use Tests\TestCase\ORM\Shared\Traits\TimestampTestTrait;

final class TimestampTest extends TestCase
{
    use MysqlConnectionTrait;
    use TimestampTestTrait;
}
