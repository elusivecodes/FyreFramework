<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema\Sqlite\Table;

use Fyre\DB\Schema\ForeignKey;
use Fyre\Utility\Collection;
use InvalidArgumentException;

trait ForeignKeyTestTrait
{
    public function testForeignKey(): void
    {
        $foreignKey = $this->schema
            ->table('test_values')
            ->foreignKey('test_values_test_id');

        $this->assertInstanceOf(ForeignKey::class, $foreignKey);

        $this->assertSame(
            'test_values_test_id',
            $foreignKey->getName()
        );

        $this->assertSame(
            [
                'test_id',
            ],
            $foreignKey->getColumns()
        );

        $this->assertSame(
            'test',
            $foreignKey->getReferencedTable()
        );

        $this->assertSame(
            [
                'id',
            ],
            $foreignKey->getReferencedColumns()
        );

        $this->assertSame(
            'CASCADE',
            $foreignKey->getOnUpdate()
        );

        $this->assertSame(
            'CASCADE',
            $foreignKey->getOnDelete()
        );
    }

    public function testForeignKeyInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table foreign key `test_values.invalid` does not exist.');

        $this->schema
            ->table('test_values')
            ->foreignKey('invalid');
    }

    public function testForeignKeys(): void
    {
        $foreignKeys = $this->schema
            ->table('test_values')
            ->foreignKeys();

        $this->assertInstanceOf(Collection::class, $foreignKeys);

        $this->assertSame(
            [
                'test_values_test_id' => [
                    'name' => 'test_values_test_id',
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
            $foreignKeys->map(
                static fn(ForeignKey $foreignKey): array => $foreignKey->toArray()
            )->toArray()
        );
    }

    public function testHasForeignKey(): void
    {
        $this->assertTrue(
            $this->schema
                ->table('test_values')
                ->hasForeignKey('test_values_test_id')
        );
    }

    public function testHasForeignKeyInvalid(): void
    {
        $this->assertFalse(
            $this->schema
                ->table('test_values')
                ->hasForeignKey('invalid')
        );
    }
}
