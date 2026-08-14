<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql;

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
            'v_bigint' => 'CAST(18446744073709551615 AS UNSIGNED INT)',
            // 'v_boolean' => 'CAST(1 AS BOOLEAN)',
            'v_date' => 'CAST(LOCALTIMESTAMP() AS DATE)',
            'v_decimal' => 'CAST(1 AS DECIMAL)',
            'v_double' => 'CAST(1 AS DOUBLE)',
            'v_float' => 'CAST(1 AS FLOAT)',
            'v_integer' => 'CAST(4294967295 AS UNSIGNED INT)',
            'v_mediumint' => 'CAST(16777215 AS UNSIGNED INT)',
            'v_smallint' => 'CAST(65535 AS UNSIGNED INT)',
            'v_tinyint' => 'CAST(255 AS UNSIGNED INT)',
            'v_time' => 'CAST(LOCALTIMESTAMP() AS TIME)',
            'v_timestamp' => 'LOCALTIMESTAMP()',
        ])
            ->execute();

        $this->assertInstanceOf(
            IntegerType::class,
            $result->getType('v_bigint')
        );

        $this->assertInstanceOf(
            DateType::class,
            $result->getType('v_date')
        );

        $this->assertInstanceOf(
            DecimalType::class,
            $result->getType('v_decimal')
        );

        $this->assertInstanceOf(
            FloatType::class,
            $result->getType('v_double')
        );

        $this->assertInstanceOf(
            FloatType::class,
            $result->getType('v_float')
        );

        $this->assertInstanceOf(
            IntegerType::class,
            $result->getType('v_integer')
        );

        $this->assertInstanceOf(
            IntegerType::class,
            $result->getType('v_mediumint')
        );

        $this->assertInstanceOf(
            IntegerType::class,
            $result->getType('v_smallint')
        );

        $this->assertInstanceOf(
            IntegerType::class,
            $result->getType('v_tinyint')
        );

        $this->assertInstanceOf(
            TimeType::class,
            $result->getType('v_time')
        );

        $this->assertInstanceOf(
            DateTimeType::class,
            $result->getType('v_timestamp')
        );
    }
}
