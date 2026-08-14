<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Sqlite;

use Fyre\DB\Types\StringType;

trait ResultTypeTestTrait
{
    public function testType(): void
    {
        $this->assertInstanceOf(
            StringType::class,
            $this->modelRegistry->use('Timestamps')
                ->find(fields: [
                    'created' => 'Timestamps.created',
                ])
                ->getResult()
                ->getType('created')
        );
    }

    public function testTypeVirtualField(): void
    {
        $this->assertInstanceOf(
            StringType::class,
            $this->modelRegistry->use('Items')
                ->find(fields: [
                    'virtual_field' => 'CURRENT_TIMESTAMP',
                ])
                ->getResult()
                ->getType('virtual_field')
        );
    }
}
