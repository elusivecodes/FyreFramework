<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Shared;

use Fyre\DB\DecoratedResultSet;
use Fyre\DB\Types\StringType;
use stdClass;

trait ResultSetTestTrait
{
    public function testAll(): void
    {
        $this->insert();

        $this->assertArraysAreIdentical(
            [
                [
                    'id' => 1,
                    'name' => 'Test 1',
                ],
                [
                    'id' => 2,
                    'name' => 'Test 2',
                ],
                [
                    'id' => 3,
                    'name' => 'Test 3',
                ],
            ],
            $this->db->select()
                ->from('test')
                ->execute()
                ->all()
        );
    }

    public function testClearBuffer(): void
    {
        $this->insert();

        $result = $this->db->select()
            ->from('test')
            ->execute();

        $result->all();
        $result->clearBuffer(1);

        $this->assertArraysAreIdentical(
            [
                [
                    'id' => 1,
                    'name' => 'Test 1',
                ],
                2 => [
                    'id' => 3,
                    'name' => 'Test 3',
                ],
            ],
            $result->all()
        );

        $this->assertNull(
            $result->fetch(1)
        );
        $this->assertSame(
            3,
            $result->count()
        );
    }

    public function testClearBufferAll(): void
    {
        $this->insert();

        $result = $this->db->select()
            ->from('test')
            ->execute();

        $result->first();
        $result->clearBuffer();

        $this->assertArraysAreIdentical(
            [
                1 => [
                    'id' => 2,
                    'name' => 'Test 2',
                ],
                [
                    'id' => 3,
                    'name' => 'Test 3',
                ],
            ],
            $result->all()
        );

        $this->assertSame(
            3,
            $result->count()
        );
    }

    public function testColumnCount(): void
    {
        $this->insert();

        $this->assertSame(
            2,
            $this->db->select()
                ->from('test')
                ->execute()
                ->columnCount()
        );
    }

    public function testColumns(): void
    {
        $this->insert();

        $this->assertArraysAreIdentical(
            [
                'id',
                'name',
            ],
            $this->db->select()
                ->from('test')
                ->execute()
                ->columns()
        );
    }

    public function testCount(): void
    {
        $this->insert();

        $this->assertSame(
            3,
            $this->db->select()
                ->from('test')
                ->execute()
                ->count()
        );
    }

    public function testDecorate(): void
    {
        $this->insert();

        $result = $this->db->select()
            ->from('test')
            ->execute()
            ->decorate(static fn(array $row): stdClass => (object) $row)
            ->decorate(static fn(stdClass $row): string => $row->name);

        $this->assertInstanceOf(
            DecoratedResultSet::class,
            $result
        );

        $this->assertArraysAreIdentical(
            [
                'Test 1',
                'Test 2',
                'Test 3',
            ],
            $result->all()
        );
    }

    public function testDecorateAfterBuffering(): void
    {
        $this->insert();

        $result = $this->db->select()
            ->from('test')
            ->execute();

        $result->all();

        $decorated = $result->decorate(static fn(array $row): string => $row['name']);

        $this->assertArraysAreIdentical(
            [
                'Test 1',
                'Test 2',
                'Test 3',
            ],
            $decorated->all()
        );
    }

    public function testDecorateCallbackOnce(): void
    {
        $this->insert();

        $count = 0;
        $result = $this->db->select()
            ->from('test')
            ->execute()
            ->decorate(static function(array $row) use (&$count): stdClass {
                $count++;

                return (object) $row;
            });

        $first = $result->first();

        $this->assertInstanceOf(
            stdClass::class,
            $first
        );

        $this->assertSame(
            $first,
            $result->fetch()
        );

        $result->all();

        $this->assertSame(
            3,
            $count
        );
    }

    public function testDecorateClearBuffer(): void
    {
        $this->insert();

        $count = 0;
        $result = $this->db->select()
            ->from('test')
            ->execute()
            ->decorate(static function(array $row) use (&$count): string {
                $count++;

                return $row['name'];
            });

        $result->first();
        $result->clearBuffer(0);

        $this->assertArraysAreIdentical(
            [
                1 => 'Test 2',
                'Test 3',
            ],
            $result->all()
        );

        $this->assertSame(
            3,
            $count
        );
        $this->assertSame(
            3,
            $result->count()
        );
    }

    public function testDecorateLazyCount(): void
    {
        $this->insert();

        $count = 0;
        $result = $this->db->select()
            ->from('test')
            ->execute()
            ->decorate(static function(array $row) use (&$count): array {
                $count++;

                return $row;
            });

        $this->assertSame(
            3,
            $result->count()
        );

        $this->assertSame(
            0,
            $count
        );
    }

    public function testDecorateMultiple(): void
    {
        $this->insert();

        $result = $this->db->select()
            ->from('test')
            ->execute()
            ->decorate(static function(array $row): array {
                $row['value'] = $row['id'];

                return $row;
            })
            ->decorate(static function(array $row): array {
                $row['value'] *= 2;

                return $row;
            });

        $this->assertArraysAreIdentical(
            [
                [
                    'id' => 1,
                    'name' => 'Test 1',
                    'value' => 2,
                ],
                [
                    'id' => 2,
                    'name' => 'Test 2',
                    'value' => 4,
                ],
                [
                    'id' => 3,
                    'name' => 'Test 3',
                    'value' => 6,
                ],
            ],
            $result->all()
        );
    }

    public function testFetch(): void
    {
        $this->insert();

        $row = $this->db->select()
            ->from('test')
            ->execute()
            ->fetch(1);

        $this->assertIsArray($row);
        $this->assertArraysAreIdentical(
            [
                'id' => 2,
                'name' => 'Test 2',
            ],
            $row
        );
    }

    public function testFirst(): void
    {
        $this->insert();

        $row = $this->db->select()
            ->from('test')
            ->execute()
            ->first();

        $this->assertIsArray($row);
        $this->assertArraysAreIdentical(
            [
                'id' => 1,
                'name' => 'Test 1',
            ],
            $row
        );
    }

    public function testIteration(): void
    {
        $this->insert();

        $query = $this->db->select()
            ->from('test')
            ->execute();

        $results = [];

        foreach ($query as $row) {
            $results[] = $row;
        }

        $this->assertArraysAreIdentical(
            [
                [
                    'id' => 1,
                    'name' => 'Test 1',
                ],
                [
                    'id' => 2,
                    'name' => 'Test 2',
                ],
                [
                    'id' => 3,
                    'name' => 'Test 3',
                ],
            ],
            $results
        );
    }

    public function testLast(): void
    {
        $this->insert();

        $row = $this->db->select()
            ->from('test')
            ->execute()
            ->last();

        $this->assertIsArray($row);
        $this->assertArraysAreIdentical(
            [
                'id' => 3,
                'name' => 'Test 3',
            ],
            $row
        );
    }

    public function testType(): void
    {
        $this->insert();

        $this->assertInstanceOf(
            StringType::class,
            $this->db->select()
                ->from('test')
                ->execute()
                ->getType('name')
        );
    }
}
