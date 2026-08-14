<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Mysql\Query;

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
            1,
            $this->db->insertId()
        );
    }
}
