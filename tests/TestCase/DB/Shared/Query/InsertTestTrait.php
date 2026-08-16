<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Shared\Query;

use Fyre\DB\Exceptions\DbException;

use function array_reverse;

trait InsertTestTrait
{
    public function testInsert(): void
    {
        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test',
                ],
            ])
            ->execute();

        $this->assertSame(
            [
                'id' => 1,
                'name' => 'Test',
            ],
            $this->db->select()
                ->from('test')
                ->execute()
                ->first()
        );
    }

    public function testInsertAffectedRows(): void
    {
        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test',
                ],
            ])
            ->execute();

        $this->assertSame(
            1,
            $this->db->affectedRows()
        );
    }

    public function testInsertBatch(): void
    {
        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test 1',
                ],
                [
                    'name' => 'Test 2',
                ],
            ])
            ->execute();

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
            ],
            $this->db->select()
                ->from('test')
                ->execute()
                ->all()
        );
    }

    public function testInsertBatchAffectedRows(): void
    {
        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test 1',
                ],
                [
                    'name' => 'Test 2',
                ],
            ])
            ->execute();

        $this->assertSame(
            2,
            $this->db->affectedRows()
        );
    }

    public function testInsertBatchColumnOrderPropertyCases(): void
    {
        $rows = [];
        $expected = [];

        for ($id = 10; $id < 26; $id++) {
            $row = [
                'id' => $id,
                'name' => 'Test '.$id,
            ];

            $rows[] = ($id % 2) === 0 ?
                $row :
                array_reverse($row, true);
            $expected[] = $row;
        }

        $this->db->insert()
            ->into('test')
            ->values($rows)
            ->execute();

        $this->assertArraysAreIdentical(
            $expected,
            $this->db->select()
                ->from('test')
                ->orderBy('id ASC')
                ->execute()
                ->all()
        );
    }

    public function testInsertId(): void
    {
        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test',
                ],
            ])
            ->execute();

        $this->assertSame(
            1,
            $this->db->insertId()
        );

        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test 2',
                ],
            ])
            ->execute();

        $this->assertSame(
            2,
            $this->db->insertId()
        );
    }

    public function testInsertMultipleTables(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionMessage('Multiple tables are not supported for this query.');

        $this->db->insert()
            ->table([
                'test',
                'test2',
            ]);
    }

    public function testInsertTableAliases(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionMessage('Table aliases are not supported for this query.');

        $this->db->insert()
            ->table([
                'alt' => 'test',
            ]);
    }

    public function testInsertVirtualTables(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionMessage('Virtual tables are not supported for this query.');

        $this->db->insert()
            ->table([
                'alt' => $this->db->select()
                    ->from('test'),
            ]);
    }
}
