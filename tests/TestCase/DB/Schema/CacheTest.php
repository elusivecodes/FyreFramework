<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema;

use Fyre\DB\Expressions\LiteralExpression;
use PHPUnit\Framework\TestCase;
use Tests\TestCase\DB\Schema\Mysql\MysqlConnectionTrait;

final class CacheTest extends TestCase
{
    use MysqlConnectionTrait;

    public function testCacheColumns(): void
    {
        $this->schema
            ->table('test')
            ->columns();

        $this->assertEquals(
            [
                'id' => [
                    'type' => 'int',
                    'length' => null,
                    'precision' => 11,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => null,
                    'charset' => null,
                    'collation' => null,
                    'comment' => '',
                    'autoIncrement' => true,
                ],
                'name' => [
                    'type' => 'varchar',
                    'length' => 255,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => true,
                    'unsigned' => false,
                    'default' => null,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'value' => [
                    'type' => 'int',
                    'length' => null,
                    'precision' => 11,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => 5,
                    'charset' => null,
                    'collation' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'price' => [
                    'type' => 'decimal',
                    'length' => null,
                    'precision' => 10,
                    'scale' => 2,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => false,
                    'unsigned' => true,
                    'default' => 2.5,
                    'charset' => null,
                    'collation' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'text' => [
                    'type' => 'varchar',
                    'length' => 255,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => 'default',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'test' => [
                    'type' => 'enum',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => [
                        'Y',
                        'N',
                    ],
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => 'Y',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'bool' => [
                    'type' => 'tinyint',
                    'length' => null,
                    'precision' => 1,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => false,
                    'charset' => null,
                    'collation' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'date_precision' => [
                    'type' => 'datetime',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => 6,
                    'values' => null,
                    'nullable' => true,
                    'unsigned' => false,
                    'default' => null,
                    'charset' => null,
                    'collation' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'created' => [
                    'type' => 'datetime',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => false,
                    'unsigned' => false,
                    'default' => new LiteralExpression('CURRENT_TIMESTAMP'),
                    'charset' => null,
                    'collation' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                ],
                'modified' => [
                    'type' => 'datetime',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'values' => null,
                    'nullable' => true,
                    'unsigned' => false,
                    'default' => new LiteralExpression('CURRENT_TIMESTAMP'),
                    'charset' => null,
                    'collation' => null,
                    'comment' => '',
                    'autoIncrement' => false,
                ],
            ],
            $this->cacher->get('test.test.columns')
        );
    }

    public function testCacheForeignKeys(): void
    {
        $this->schema
            ->table('test_values')
            ->foreignKeys();

        $this->assertSame(
            [
                'test_values_test_id' => [
                    'columns' => [
                        'test_id',
                    ],
                    'referencedTable' => 'test',
                    'referencedColumns' => [
                        'id',
                    ],
                    'onUpdate' => 'CASCADE',
                    'onDelete' => 'CASCADE',
                ],
            ],
            $this->cacher->get('test.test_values.foreign_keys')
        );
    }

    public function testCacheIndexes(): void
    {
        $this->schema
            ->table('test')
            ->indexes();

        $this->assertSame(
            [
                'PRIMARY' => [
                    'columns' => [
                        'id',
                    ],
                    'unique' => true,
                    'primary' => true,
                    'type' => 'btree',
                ],
                'name' => [
                    'columns' => [
                        'name',
                    ],
                    'unique' => true,
                    'primary' => false,
                    'type' => 'btree',
                ],
                'name_value' => [
                    'columns' => [
                        'name',
                        'value',
                    ],
                    'unique' => false,
                    'primary' => false,
                    'type' => 'btree',
                ],
            ],
            $this->cacher->get('test.test.indexes')
        );
    }

    public function testCacheTables(): void
    {
        $this->schema->tables();

        $this->assertSame(
            [
                'test' => [
                    'engine' => 'InnoDB',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'comment' => '',
                ],
                'test_values' => [
                    'engine' => 'InnoDB',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'comment' => '',
                ],
            ],
            $this->cacher->get('test.tables')
        );
    }

    public function testSchemaClearDeletesCachedTableMetadata(): void
    {
        $this->schema->tables();
        $this->schema->table('test')->columns();
        $this->schema->table('test')->indexes();
        $this->schema->table('test_values')->foreignKeys();

        $this->assertNotNull($this->cacher->get('test.tables'));
        $this->assertNotNull($this->cacher->get('test.test.columns'));
        $this->assertNotNull($this->cacher->get('test.test.indexes'));
        $this->assertNotNull($this->cacher->get('test.test_values.foreign_keys'));

        $this->schema->clear();

        $this->assertNull($this->cacher->get('test.tables'));
        $this->assertNull($this->cacher->get('test.test.columns'));
        $this->assertNull($this->cacher->get('test.test.indexes'));
        $this->assertNull($this->cacher->get('test.test_values.foreign_keys'));
    }

    public function testTableClearDeletesOnlyCurrentTableMetadata(): void
    {
        $table = $this->schema->table('test');

        $table->columns();
        $table->indexes();
        $this->schema->table('test_values')->foreignKeys();

        $this->assertNotNull($this->cacher->get('test.test.columns'));
        $this->assertNotNull($this->cacher->get('test.test.indexes'));
        $this->assertNotNull($this->cacher->get('test.test_values.foreign_keys'));

        $table->clear();

        $this->assertNull($this->cacher->get('test.test.columns'));
        $this->assertNull($this->cacher->get('test.test.indexes'));
        $this->assertNotNull($this->cacher->get('test.test_values.foreign_keys'));
    }
}
