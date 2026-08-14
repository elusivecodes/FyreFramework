<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Postgres\Model;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\Postgres\PostgresConnectionTrait;
use Tests\TestCase\ORM\Shared\Model\ModelTestTrait;

final class ModelTest extends TestCase
{
    use BelongsToSqlTestTrait;
    use ContainSqlTestTrait;
    use HasManySqlTestTrait;
    use HasOneSqlTestTrait;
    use JoinSqlTestTrait;
    use ManyToManySqlTestTrait;
    use MatchingSqlTestTrait;
    use ModelTestTrait;
    use PostgresConnectionTrait;
    use QuerySqlTestTrait;
    use RelationshipSqlTestTrait;
}
