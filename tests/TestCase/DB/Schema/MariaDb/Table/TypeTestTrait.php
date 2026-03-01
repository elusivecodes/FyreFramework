<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema\MariaDb\Table;

use Fyre\DB\Types\BooleanType;
use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\StringType;

trait TypeTestTrait
{
    public function testGetType(): void
    {
        $this->assertInstanceOf(
            StringType::class,
            $this->schema->table('test')
                ->column('name')
                ->type()
        );
    }

    public function testGetTypeBoolean(): void
    {
        $this->assertInstanceOf(
            BooleanType::class,
            $this->schema->table('test')
                ->column('bool')
                ->type()
        );
    }

    public function testGetTypeDateTime(): void
    {
        $this->assertInstanceOf(
            DateTimeType::class,
            $this->schema->table('test')
                ->column('created')
                ->type()
        );
    }

    public function testGetTypeDecimal(): void
    {
        $this->assertInstanceOf(
            DecimalType::class,
            $this->schema->table('test')
                ->column('price')
                ->type()
        );
    }
}
