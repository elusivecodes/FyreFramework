<?php
declare(strict_types=1);

namespace Tests\TestCase\DB;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Connection;
use Fyre\DB\ConnectionManager;
use Fyre\DB\ConnectionRetry;
use Fyre\DB\Expressions\CaseExpression;
use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Expressions\FunctionBuilder;
use Fyre\DB\Expressions\FunctionExpression;
use Fyre\DB\Expressions\IdentifierExpression;
use Fyre\DB\Expressions\LiteralExpression;
use Fyre\DB\Expressions\WindowExpression;
use Fyre\DB\Queries\DeleteQuery;
use Fyre\DB\Queries\InsertQuery;
use Fyre\DB\Queries\SelectQuery;
use Fyre\DB\Queries\UpdateQuery;
use Fyre\DB\Queries\UpsertQuery;
use Fyre\DB\Query;
use Fyre\DB\QueryGenerator;
use Fyre\DB\ResultSet;
use Fyre\DB\ValueBinder;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class TraitsTest extends TestCase
{
    /**
     * @return array<string, array{class-string}>
     */
    public static function debugClassProvider(): array
    {
        return [
            'case expression' => [CaseExpression::class],
            'condition expression' => [ConditionExpression::class],
            'connection manager' => [ConnectionManager::class],
            'connection retry' => [ConnectionRetry::class],
            'function builder' => [FunctionBuilder::class],
            'function expression' => [FunctionExpression::class],
            'identifier expression' => [IdentifierExpression::class],
            'literal expression' => [LiteralExpression::class],
            'query' => [Query::class],
            'query generator' => [QueryGenerator::class],
            'result set' => [ResultSet::class],
            'value binder' => [ValueBinder::class],
            'window expression' => [WindowExpression::class],
        ];
    }

    /**
     * @return array<string, array{class-string}>
     */
    public static function macroClassProvider(): array
    {
        return [
            'connection' => [Connection::class],
            'delete query' => [DeleteQuery::class],
            'insert query' => [InsertQuery::class],
            'result set' => [ResultSet::class],
            'select query' => [SelectQuery::class],
            'update query' => [UpdateQuery::class],
            'upsert query' => [UpsertQuery::class],
        ];
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('debugClassProvider')]
    public function testDebug(string $class): void
    {
        $this->assertContains(DebugTrait::class, class_uses($class));
    }

    /**
     * @param class-string $class
     */
    #[DataProvider('macroClassProvider')]
    public function testMacro(string $class): void
    {
        $this->assertContains(MacroTrait::class, class_uses($class));
    }
}
