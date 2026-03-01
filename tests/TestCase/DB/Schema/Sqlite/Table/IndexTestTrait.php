<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Schema\Sqlite\Table;

use Fyre\DB\Schema\Index;
use Fyre\Utility\Collection;
use InvalidArgumentException;

trait IndexTestTrait
{
    public function testHasIndex(): void
    {
        $this->assertTrue(
            $this->schema
                ->table('test')
                ->hasIndex('name')
        );
    }

    public function testHasIndexInvalid(): void
    {
        $this->assertFalse(
            $this->schema
                ->table('test')
                ->hasIndex('invalid')
        );
    }

    public function testIndex(): void
    {
        $index = $this->schema
            ->table('test')
            ->index('name');

        $this->assertInstanceOf(Index::class, $index);

        $this->assertSame(
            'name',
            $index->getName()
        );

        $this->assertSame(
            [
                'name',
            ],
            $index->getColumns()
        );

        $this->assertTrue(
            $index->isUnique()
        );

        $this->assertFalse(
            $index->isPrimary()
        );

        $this->assertNull(
            $index->getType()
        );
    }

    public function testIndexes(): void
    {
        $indexes = $this->schema
            ->table('test')
            ->indexes();

        $this->assertInstanceOf(Collection::class, $indexes);

        $this->assertSame(
            [
                'primary' => [
                    'name' => 'primary',
                    'columns' => [
                        'id',
                    ],
                    'unique' => true,
                    'primary' => true,
                    'type' => null,
                ],
                'name' => [
                    'name' => 'name',
                    'columns' => [
                        'name',
                    ],
                    'unique' => true,
                    'primary' => false,
                    'type' => null,
                ],
                'name_value' => [
                    'name' => 'name_value',
                    'columns' => [
                        'name',
                        'value',
                    ],
                    'unique' => false,
                    'primary' => false,
                    'type' => null,
                ],
            ],
            $indexes->map(
                static fn(Index $index): array => $index->toArray()
            )->toArray()
        );
    }

    public function testIndexInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Table index `test.invalid` does not exist');

        $this->schema
            ->table('test')
            ->index('invalid');
    }

    public function testPrimaryKey(): void
    {
        $this->assertSame(
            [
                'id',
            ],
            $this->schema
                ->table('test')
                ->primaryKey()
        );
    }
}
