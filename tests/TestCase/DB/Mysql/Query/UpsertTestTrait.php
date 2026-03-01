<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql\Query;

use Fyre\DB\Exceptions\DbException;

trait UpsertTestTrait
{
    public function testUpsert(): void
    {
        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'name' => 'Test',
                ],
            ])
            ->execute();

        $this->db->upsert(['id'])
            ->into('test')
            ->values([
                [
                    'id' => 1,
                    'name' => 'Test 2',
                ],
            ])
            ->execute();

        $this->assertSame(
            [
                'id' => 1,
                'name' => 'Test 2',
            ],
            $this->db->select()
                ->from('test')
                ->execute()
                ->first()
        );
    }

    public function testUpsertBatch(): void
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

        $this->db->upsert(['id'])
            ->into('test')
            ->values([
                [
                    'id' => 1,
                    'name' => 'Test 3',
                ],
                [
                    'id' => 2,
                    'name' => 'Test 4',
                ],
            ])
            ->execute();

        $this->assertSame(
            [
                [
                    'id' => 1,
                    'name' => 'Test 3',
                ],
                [
                    'id' => 2,
                    'name' => 'Test 4',
                ],
            ],
            $this->db->select()
                ->from('test')
                ->execute()
                ->all()
        );
    }

    public function testUpsertMultipleTables(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionMessage('Multiple tables are not supported for this query.');

        $this->db->upsert(['id'])
            ->table([
                'test',
                'test2',
            ]);
    }

    public function testUpsertTableAliases(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionMessage('Table aliases are not supported for this query.');

        $this->db->upsert(['id'])
            ->table([
                'alt' => 'test',
            ]);
    }

    public function testUpsertVirtualTables(): void
    {
        $this->expectException(DbException::class);
        $this->expectExceptionMessage('Virtual tables are not supported for this query.');

        $this->db->upsert(['id'])
            ->table([
                'alt' => $this->db->select()
                    ->from('test'),
            ]);
    }
}
