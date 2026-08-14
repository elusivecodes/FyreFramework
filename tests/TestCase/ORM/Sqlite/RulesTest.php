<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Sqlite;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\Shared\RulesTestTrait;

final class RulesTest extends TestCase
{
    use RulesTestTrait;
    use SqliteConnectionTrait;
}
