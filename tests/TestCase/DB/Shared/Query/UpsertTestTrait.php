<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Shared\Query;

use Fyre\DB\Exceptions\DbException;

trait UpsertTestTrait
{
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
