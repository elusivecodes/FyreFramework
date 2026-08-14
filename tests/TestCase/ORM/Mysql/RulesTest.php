<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Mysql;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\Shared\RulesTestTrait;

final class RulesTest extends TestCase
{
    use MysqlConnectionTrait;
    use RulesTestTrait;
}
