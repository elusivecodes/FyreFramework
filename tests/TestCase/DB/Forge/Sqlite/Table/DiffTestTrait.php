<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Sqlite\Table;

use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\JsonType;
use Fyre\DB\Types\StringType;
use Fyre\DB\Types\TextType;
use RuntimeException;

trait DiffTestTrait
{
    public function testTableDiffChangeForeignKey(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Foreign keys cannot be dropped from existing tables.');

        $this->forge->createTable('test_values', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ], [
            'primary' => [
                'columns' => [
                    'id',
                ],
                'primary' => true,
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
            'test_value_id' => [
                'columns' => 'value_id',
                'referencedTable' => 'test_values',
                'referencedColumns' => 'id',
            ],
        ]);

        $this->forge
            ->build('test')
            ->clear()
            ->addColumn('id', [
                'type' => IntegerType::class,
            ])
            ->addColumn('value_id', [
                'type' => IntegerType::class,
            ])
            ->addForeignKey('test_value_id', [
                'columns' => 'value_id',
                'referencedTable' => 'test_values',
                'referencedColumns' => 'id',
                'onUpdate' => 'CASCADE',
                'onDelete' => 'CASCADE',
            ])
            ->sql();
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
                'DROP INDEX value',
                'CREATE UNIQUE INDEX value ON test (value)',
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
            'primary' => [
                'columns' => [
                    'id',
                ],
                'primary' => true,
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
                'ALTER TABLE test ADD COLUMN value1 VARCHAR(80) NOT NULL',
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
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Foreign keys cannot be added to existing tables.');

        $this->forge->createTable('test_values', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ], [
            'primary' => [
                'columns' => [
                    'id',
                ],
                'primary' => true,
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

        $this->forge
            ->build('test')
            ->clear()
            ->addColumn('id', [
                'type' => IntegerType::class,
            ])
            ->addColumn('value_id', [
                'type' => IntegerType::class,
            ])
            ->addForeignKey('test_value_id', [
                'columns' => 'value_id',
                'referencedTable' => 'test_values',
                'referencedColumns' => 'id',
            ])
            ->sql();
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
                'CREATE INDEX value ON test (value)',
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

    public function testTableDiffSqlChangeColumn(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Columns cannot be modified in existing tables.');

        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
            'value' => [
                'type' => StringType::class,
            ],
        ]);

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
            ->sql();
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
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Foreign keys cannot be dropped from existing tables.');

        $this->forge->createTable('test_values', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ], [
            'primary' => [
                'columns' => [
                    'id',
                ],
                'primary' => true,
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

        $this->forge
            ->build('test')
            ->clear()
            ->addColumn('id', [
                'type' => IntegerType::class,
            ])
            ->addColumn('value_id', [
                'type' => IntegerType::class,
            ])
            ->sql();
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
                'DROP INDEX value',
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
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Primary keys cannot be added to existing tables.');

        $this->forge->createTable('test', [
            'id' => [
                'type' => IntegerType::class,
            ],
        ]);

        $this->forge
            ->build('test')
            ->clear()
            ->addColumn('id', [
                'type' => IntegerType::class,
            ])
            ->setPrimaryKey('id')
            ->sql();
    }
}
