<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Sqlite;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\Shared\ResultTestTrait;

final class ResultTest extends TestCase
{
    use ResultTestTrait;
    use ResultTypeTestTrait;
    use SqliteConnectionTrait;
}
