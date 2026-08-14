<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Sqlite\Traits;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\Shared\Traits\TimestampTestTrait;
use Tests\TestCase\ORM\Sqlite\SqliteConnectionTrait;

final class TimestampTest extends TestCase
{
    use SqliteConnectionTrait;
    use TimestampTestTrait;
}
