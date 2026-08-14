<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\MariaDb\Traits;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\MariaDb\MariaDbConnectionTrait;
use Tests\TestCase\ORM\Mysql\Traits\SoftDeleteSqlTestTrait;
use Tests\TestCase\ORM\Shared\Traits\SoftDeleteTestTrait;

final class SoftDeleteTest extends TestCase
{
    use MariaDbConnectionTrait;
    use SoftDeleteSqlTestTrait;
    use SoftDeleteTestTrait;
}
