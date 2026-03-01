<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Postgres\Forge;

trait DropSchemaTestTrait
{
    public function testDropSchema(): void
    {
        $this->forge->createSchema('other');

        $this->forge->dropSchema('other');

        $this->assertCount(
            0,
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
