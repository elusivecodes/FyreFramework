<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Sqlite\Sql;

use Fyre\Core\Container;
use Fyre\DB\Connection;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\DB\TestSqliteConnection;

final class SqlTest extends TestCase
{
    use AggregateTestTrait;
    use CaseTestTrait;
    use ConditionTestTrait;
    use DeleteTestTrait;
    use ExceptTestTrait;
    use FunctionTestTrait;
    use GroupLimitTestTrait;
    use HavingTestTrait;
    use InsertFromTestTrait;
    use InsertTestTrait;
    use IntersectTestTrait;
    use JoinTestTrait;
    use SelectTestTrait;
    use UnionAllTestTrait;
    use UnionTestTrait;
    use UpdateBatchTestTrait;
    use UpdateTestTrait;
    use UpsertTestTrait;
    use WhereTestTrait;
    use WindowTestTrait;
    use WithTestTrait;

    protected Connection $db;

    public function testGetConnection(): void
    {
        $this->assertSame(
            $this->db,
            $this->db->select()
                ->getConnection()
        );
    }

    public function testToString(): void
    {
        $this->assertSame(
            'SELECT * FROM "test"',
            (string) $this->db->select()
                ->from('test')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();

        $this->db = $container->build(TestSqliteConnection::class);
    }
}
