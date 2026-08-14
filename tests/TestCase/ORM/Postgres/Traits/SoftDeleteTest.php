<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Postgres\Traits;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\Postgres\PostgresConnectionTrait;
use Tests\TestCase\ORM\Shared\Traits\SoftDeleteTestTrait;

final class SoftDeleteTest extends TestCase
{
    use PostgresConnectionTrait;
    use SoftDeleteSqlTestTrait;
    use SoftDeleteTestTrait;
}
