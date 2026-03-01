<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Postgres\Forge;

trait CreateSchemaTestTrait
{
    public function testCreateSchema(): void
    {
        $this->forge->createSchema('other');

        $this->assertCount(
            1,
            $this->db->select()
                ->from('INFORMATION_SCHEMA.SCHEMATA')
                ->where([
                    'SCHEMA_NAME' => 'other',
                ])
                ->execute()
                ->all()
        );
    }
}
