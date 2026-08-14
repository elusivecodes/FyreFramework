<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Postgres;

use Fyre\DB\Types\BooleanType;
use Fyre\DB\Types\DateTimeTimeZoneType;
use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\DateType;
use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\TimeType;

trait ResultSetTypeTestTrait
{
    public function testTypeVirtualField(): void
    {
        $result = $this->db->select([
            'v_bigint' => 'CAST(9223372036854775807 AS BIGINT)',
            'v_boolean' => 'CAST(1 AS BOOLEAN)',
            'v_date' => 'CAST(LOCALTIMESTAMP(0) AS DATE)',
            'v_double' => 'CAST(1 AS DOUBLE PRECISION)',
            'v_integer' => 'CAST(2147483647 AS INTEGER)',
            'v_money' => 'CAST(1 AS MONEY)',
            'v_numeric' => 'CAST(1 AS NUMERIC)',
            'v_real' => 'CAST(1 AS REAL)',
            'v_smallint' => 'CAST(32767 AS SMALLINT)',
            'v_time' => 'LOCALTIME(0)',
            'v_timestamp' => 'LOCALTIMESTAMP(0)',
            'v_timestamp_tz' => 'CURRENT_TIMESTAMP(0)',
        ])
            ->execute();

        $this->assertInstanceOf(
            IntegerType::class,
            $result->getType('v_bigint')
        );

        $this->assertInstanceOf(
            BooleanType::class,
            $result->getType('v_boolean')
        );

        $this->assertInstanceOf(
            DateType::class,
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
            DecimalType::class,
            $result->getType('v_money')
        );

        $this->assertInstanceOf(
            DecimalType::class,
            $result->getType('v_numeric')
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
            TimeType::class,
            $result->getType('v_time')
        );

        $this->assertInstanceOf(
            DateTimeType::class,
            $result->getType('v_timestamp')
        );

        $this->assertInstanceOf(
            DateTimeTimeZoneType::class,
            $result->getType('v_timestamp_tz')
        );
    }
}
