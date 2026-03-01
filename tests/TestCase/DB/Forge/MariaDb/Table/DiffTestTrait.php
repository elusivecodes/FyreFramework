<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\MariaDb\Table;

use Fyre\DB\QueryLiteral;
use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\JsonType;
use Fyre\DB\Types\StringType;
use Fyre\DB\Types\TextType;

trait DiffTestTrait
{
    public function testTableDiffChangeForeignKey(): void
    {
        $this->forge->createTable('test_values', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ], [
            'PRIMARY' => [
                'columns' => [
                    'id',
                ],
            ],
        ]);

        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value_id' => [
                'type' => IntegerType::class,
            ],
        ], foreignKeys: [
            'value_id' => [
                'referencedTable' => 'test_values',
                'referencedColumns' => 'id',
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test DROP FOREIGN KEY value_id, ADD CONSTRAINT value_id FOREIGN KEY (value_id) REFERENCES test_values (id) ON UPDATE CASCADE ON DELETE CASCADE',
            ],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->addColumn('value_id', [
                    'type' => IntegerType::class,
                ])
                ->addForeignKey('value_id', [
                    'referencedTable' => 'test_values',
                    'referencedColumns' => 'id',
                    'onUpdate' => 'CASCADE',
                    'onDelete' => 'CASCADE',
                ])
                ->sql()
        );
    }

    public function testTableDiffChangeIndex(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => StringType::class,
            ],
        ], [
            'value' => [],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test DROP INDEX value, ADD CONSTRAINT value UNIQUE KEY (value) USING BTREE',
            ],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->addColumn('value', [
                    'type' => StringType::class,
                ])
                ->addIndex('value', [
                    'unique' => true,
                ])
                ->sql()
        );
    }

    public function testTableDiffSql(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
                'autoIncrement' => true,
            ],
            'value' => [
                'type' => StringType::class,
                'length' => 255,
            ],
            'json_default' => [
                'type' => JsonType::class,
                'default' => '{"key": "value"}',
            ],
            'text_default' => [
                'type' => TextType::class,
                'default' => 'This is a default value',
            ],
            'point_default' => [
                'type' => 'POINT',
                'default' => new QueryLiteral('point(1,2)'),
            ],
            'created' => [
                'type' => DateTimeType::class,
                'default' => 'CURRENT_TIMESTAMP',
            ],
            'modified' => [
                'type' => DateTimeType::class,
                'nullable' => true,
                'default' => null,
            ],
        ], [
            'PRIMARY' => [
                'columns' => [
                    'id',
                ],
            ],
            'value' => [
                'unique' => true,
            ],
        ]);

        $this->assertSame(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('id', [
                    'type' => IntegerType::class,
                    'autoIncrement' => true,
                ])
                ->addColumn('value', [
                    'type' => StringType::class,
                    'length' => 255,
                ])
                ->addColumn('json_default', [
                    'type' => JsonType::class,
                    'default' => '{"key": "value"}',
                ])
                ->addColumn('text_default', [
                    'type' => TextType::class,
                    'default' => 'This is a default value',
                ])
                ->addColumn('point_default', [
                    'type' => 'POINT',
                    'default' => new QueryLiteral('point(1,2)'),
                ])
                ->addColumn('created', [
                    'type' => DateTimeType::class,
                    'default' => 'CURRENT_TIMESTAMP',
                ])
                ->addColumn('modified', [
                    'type' => DateTimeType::class,
                    'nullable' => true,
                    'default' => null,
                ])
                ->setPrimaryKey('id')
                ->addIndex('value', [
                    'unique' => true,
                ])
                ->sql()
        );
    }

    public function testTableDiffSqlAddColumn(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value2' => [
                'type' => StringType::class,
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test ADD COLUMN value1 VARCHAR(80) CHARACTER SET \'utf8mb4\' COLLATE \'utf8mb4_unicode_ci\' NOT NULL AFTER id',
            ],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->addColumn('value1', [
                    'type' => StringType::class,
                ])
                ->addColumn('value2', [
                    'type' => StringType::class,
                ])
                ->sql()
        );
    }

    public function testTableDiffSqlAddForeignKey(): void
    {
        $this->forge->createTable('test_values', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ], [
            'PRIMARY' => [
                'columns' => [
                    'id',
                ],
            ],
        ]);

        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value_id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test ADD CONSTRAINT value_id FOREIGN KEY (value_id) REFERENCES test_values (id)',
            ],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->addColumn('value_id', [
                    'type' => IntegerType::class,
                ])
                ->addForeignKey('value_id', [
                    'referencedTable' => 'test_values',
                    'referencedColumns' => 'id',
                ])
                ->sql()
        );
    }

    public function testTableDiffSqlAddIndex(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => StringType::class,
                'length' => 255,
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test ADD INDEX value (value) USING BTREE',
            ],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->addColumn('value', [
                    'type' => StringType::class,
                    'length' => 255,
                ])
                ->addIndex('value')
                ->sql()
        );
    }

    public function testTableDiffSqlAlterTable(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test ENGINE = MyISAM',
            ],
            $this->forge
                ->build('test', [
                    'engine' => 'MyISAM',
                ])
                ->clear()
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->sql()
        );
    }

    public function testTableDiffSqlChangeColumn(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => StringType::class,
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test CHANGE COLUMN value value VARCHAR(255) CHARACTER SET \'utf8mb4\' COLLATE \'utf8mb4_unicode_ci\' NOT NULL',
            ],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->addColumn('value', [
                    'type' => StringType::class,
                    'length' => 255,
                ])
                ->sql()
        );
    }

    public function testTableDiffSqlChangeColumnOrder(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value1' => [
                'type' => StringType::class,
            ],
            'value2' => [
                'type' => StringType::class,
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test CHANGE COLUMN value2 value2 VARCHAR(80) CHARACTER SET \'utf8mb4\' COLLATE \'utf8mb4_unicode_ci\' NOT NULL AFTER id',
            ],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->addColumn('value2', [
                    'type' => StringType::class,
                ])
                ->addColumn('value1', [
                    'type' => StringType::class,
                ])
                ->sql()
        );
    }

    public function testTableDiffSqlDropColumn(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => StringType::class,
                'length' => 255,
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test DROP COLUMN value',
            ],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->sql()
        );
    }

    public function testTableDiffSqlDropForeignKey(): void
    {
        $this->forge->createTable('test_values', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ], [
            'PRIMARY' => [
                'columns' => [
                    'id',
                ],
            ],
        ]);

        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value_id' => [
                'type' => IntegerType::class,
            ],
        ], foreignKeys: [
            'value_id' => [
                'referencedTable' => 'test_values',
                'referencedColumns' => 'id',
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test DROP FOREIGN KEY value_id',
            ],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->addColumn('value_id', [
                    'type' => IntegerType::class,
                ])
                ->sql()
        );
    }

    public function testTableDiffSqlDropIndex(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => StringType::class,
                'length' => 255,
            ],
        ], [
            'value' => [],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test DROP INDEX value',
            ],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->addColumn('value', [
                    'type' => StringType::class,
                    'length' => 255,
                ])
                ->sql()
        );
    }

    public function testTableDiffSqlPrimaryKey(): void
    {
        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->assertSame(
            [
                'ALTER TABLE test ADD PRIMARY KEY (id)',
            ],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('id', [
                    'type' => IntegerType::class,
                ])
                ->setPrimaryKey('id')
                ->sql()
        );
    }
}
