<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema\Postgres\Table;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Schema\Postgres\PostgresConnectionTrait;

final class TableTest extends TestCase
{
    use ColumnTestTrait;
    use DefaultValueTestTrait;
    use ForeignKeyTestTrait;
    use IndexTestTrait;
    use PostgresConnectionTrait;
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
