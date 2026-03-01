<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema\MariaDb\Table;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Schema\MariaDb\MariaDbConnectionTrait;

final class TableTest extends TestCase
{
    use ColumnTestTrait;
    use DefaultValueTestTrait;
    use ForeignKeyTestTrait;
    use IndexTestTrait;
    use MariaDbConnectionTrait;
    use TypeTestTrait;

    public function testGetSchema(): void
    {
        $this->assertSame(
            $this->schema,
            $this->schema
                ->table('test')
                ->getSchema()
        );
    }
}
