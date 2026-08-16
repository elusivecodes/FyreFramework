<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\MariaDb\Table;

use Fyre\DB\Types\BinaryType;
use Fyre\DB\Types\BooleanType;
use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\DateType;
use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;
use Fyre\DB\Types\TextType;
use Fyre\DB\Types\TimeType;

trait DiffDefaultsTestTrait
{
    public function testTableDiffDefaultsBigInt(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => IntegerType::class,
                'precision' => 20,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => IntegerType::class,
                    'precision' => 20,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsBinary(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => BinaryType::class,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => BinaryType::class,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsBoolean(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => BooleanType::class,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => BooleanType::class,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsChar(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => StringType::class,
                'length' => 1,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => StringType::class,
                    'length' => 1,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsDate(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => DateType::class,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => DateType::class,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsDatetime(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => DateTimeType::class,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => DateTimeType::class,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsDecimal(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => DecimalType::class,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => DecimalType::class,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsFloat(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => FloatType::class,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => FloatType::class,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsInt(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => IntegerType::class,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsLongText(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => TextType::class,
                'length' => 4_294_967_295,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => TextType::class,
                    'length' => 4_294_967_295,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsMediumInt(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => IntegerType::class,
                'precision' => 8,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => IntegerType::class,
                    'precision' => 8,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsMediumText(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => TextType::class,
                'length' => 16_777_215,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => TextType::class,
                    'length' => 16_777_215,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsSmallInt(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => IntegerType::class,
                'precision' => 6,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => IntegerType::class,
                    'precision' => 6,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsText(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => TextType::class,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => TextType::class,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsTime(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => TimeType::class,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => TimeType::class,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsTimestamp(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => 'timestamp',
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => 'timestamp',
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsTinyInt(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => IntegerType::class,
                'precision' => 4,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => IntegerType::class,
                    'precision' => 4,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsTinyText(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => TextType::class,
                'length' => 255,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => TextType::class,
                    'length' => 255,
                ])
                ->sql()
        );
    }

    public function testTableDiffDefaultsVarchar(): void
    {
        $this->forge->createTable('test', [
            'value' => [
                'type' => StringType::class,
            ],
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', [
                    'type' => StringType::class,
                ])
                ->sql()
        );
    }
}
