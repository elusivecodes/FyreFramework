<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\MariaDb\Traits;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\MariaDb\MariaDbConnectionTrait;
use Tests\TestCase\ORM\Shared\Traits\TimestampTestTrait;

final class TimestampTest extends TestCase
{
    use MariaDbConnectionTrait;
    use TimestampTestTrait;
}
