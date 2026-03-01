<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Postgres\Model;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\Postgres\PostgresConnectionTrait;

final class ModelTest extends TestCase
{
    use BelongsToCallbacksTestTrait;
    use BelongsToTestTrait;
    use CallbacksBelongsToTestTrait;
    use CallbacksHasManyTestTrait;
    use CallbacksHasOneTestTrait;
    use CallbacksManyToManyTestTrait;
    use CallbacksTestTrait;
    use ContainTestTrait;
    use HasManyCallbacksTestTrait;
    use HasManyTestTrait;
    use HasOneCallbacksTestTrait;
    use HasOneTestTrait;
    use JoinTestTrait;
    use LoadIntoTestTrait;
    use ManyToManyCallbacksTestTrait;
    use ManyToManyTestTrait;
    use MatchingTestTrait;
    use NewEntityTestTrait;
    use PatchEntityTestTrait;
    use PostgresConnectionTrait;
    use QueryTestTrait;
    use RelationshipTestTrait;
    use SchemaTestTrait;

    public function testConnection(): void
    {
        $this->assertSame(
            $this->db,
            $this->modelRegistry->use('Test')->getConnection()
        );
    }
}
