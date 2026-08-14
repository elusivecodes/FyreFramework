<?php
declare(strict_types=1);

namespace Tests\TestCase\ORM\Sqlite\Model;

use PHPUnit\Framework\TestCase;
use Tests\TestCase\ORM\Shared\Model\ModelTestTrait;
use Tests\TestCase\ORM\Sqlite\SqliteConnectionTrait;

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
    use QuerySqlTestTrait;
    use RelationshipSqlTestTrait;
    use SqliteConnectionTrait;
}
