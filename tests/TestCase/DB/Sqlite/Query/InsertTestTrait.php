<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite\Query;

trait InsertTestTrait
{
    public function testInsertBatchId(): void
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
            $this->db->insertId()
        );
    }
}
