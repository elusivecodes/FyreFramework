<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Sqlite\Traits;

use function substr_count;

trait SoftDeleteSqlTestTrait
{
    public function testJoinContainPathBuildJoinTriggeredOnce(): void
    {
        $sql = $this->modelRegistry->use('Users')
            ->find()
            ->contain([
                'Addresses' => [
                    'autoFields' => false,
                ],
            ])
            ->innerJoinWith('Addresses')
            ->disableAutoFields()
            ->sql();

        $this->assertSame(
            1,
            substr_count($sql, '"Addresses"."deleted" IS NULL')
        );
    }
}
