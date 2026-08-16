<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite\Query;

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
            ], [
                'id',
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
            ], [
                'id',
            ])
            ->execute();

        $this->assertArraysAreIdentical(
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

    public function testUpsertBatchColumnOrder(): void
    {
        $this->db->insert()
            ->into('test')
            ->values([
                [
                    'id' => 10,
                    'name' => 'Old',
                ],
            ])
            ->execute();

        $this->db->upsert(['id'])
            ->into('test')
            ->values([
                [
                    'id' => 10,
                    'name' => 'Updated',
                ],
                [
                    'name' => 'New',
                    'id' => 20,
                ],
            ], [
                'id',
            ])
            ->execute();

        $this->assertArraysAreIdentical(
            [
                [
                    'id' => 10,
                    'name' => 'Updated',
                ],
                [
                    'id' => 20,
                    'name' => 'New',
                ],
            ],
            $this->db->select()
                ->from('test')
                ->orderBy('id ASC')
                ->execute()
                ->all()
        );
    }
}
