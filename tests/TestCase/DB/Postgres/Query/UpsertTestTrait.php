<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Postgres\Query;

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
}
