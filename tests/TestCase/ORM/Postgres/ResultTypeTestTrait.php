<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Postgres;

use Fyre\DB\Types\DateTimeType;

trait ResultTypeTestTrait
{
    public function testType(): void
    {
        $this->assertInstanceOf(
            DateTimeType::class,
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
            DateTimeType::class,
            $this->modelRegistry->use('Items')
                ->find(fields: [
                    'virtual_field' => 'LOCALTIMESTAMP(0)',
                ])
                ->getResult()
                ->getType('virtual_field')
        );
    }
}
