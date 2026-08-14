<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite;

use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;

trait ResultSetTypeTestTrait
{
    public function testTypeVirtualField(): void
    {
        $result = $this->db->select([
            'v_bigint' => 'CAST(9223372036854775807 AS BIGINT)',
            'v_boolean' => 'CAST(1 AS BOOLEAN)',
            'v_date' => 'DATE()',
            'v_double' => 'CAST(1 AS DOUBLE PRECISION)',
            'v_integer' => 'CAST(2147483647 AS INTEGER)',
            'v_numeric' => 'CAST(1 AS NUMERIC)',
            'v_real' => 'CAST(1 AS REAL)',
            'v_smallint' => 'CAST(32767 AS SMALLINT)',
            'v_time' => 'TIME()',
            'v_timestamp' => 'CURRENT_TIMESTAMP',
        ])
            ->execute();

        $this->assertInstanceOf(
            IntegerType::class,
            $result->getType('v_bigint')
        );

        $this->assertInstanceOf(
            IntegerType::class,
            $result->getType('v_boolean')
        );

        $this->assertInstanceOf(
            StringType::class,
            $result->getType('v_date')
        );

        $this->assertInstanceOf(
            FloatType::class,
            $result->getType('v_double')
        );

        $this->assertInstanceOf(
            IntegerType::class,
            $result->getType('v_integer')
        );

        $this->assertInstanceOf(
            FloatType::class,
            $result->getType('v_real')
        );

        $this->assertInstanceOf(
            IntegerType::class,
            $result->getType('v_smallint')
        );

        $this->assertInstanceOf(
            StringType::class,
            $result->getType('v_time')
        );

        $this->assertInstanceOf(
            StringType::class,
            $result->getType('v_timestamp')
        );
    }
}
