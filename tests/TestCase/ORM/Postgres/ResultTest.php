<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Postgres;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\Shared\ResultTestTrait;

final class ResultTest extends TestCase
{
    use PostgresConnectionTrait;
    use ResultTestTrait;
    use ResultTypeTestTrait;
}
