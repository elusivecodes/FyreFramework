<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Mysql;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\Shared\ResultTestTrait;

final class ResultTest extends TestCase
{
    use MysqlConnectionTrait;
    use ResultTestTrait;
    use ResultTypeTestTrait;
}
