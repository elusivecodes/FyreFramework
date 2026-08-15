<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Mysql;

use Fyre\Core\Container;
use Fyre\DB\Forge\ForgeRegistry;
use Fyre\DB\Forge\Handlers\Mysql\MysqlForge;
use Fyre\DB\Forge\Handlers\Mysql\MysqlQueryGenerator;
use Fyre\DB\Forge\Handlers\Mysql\MysqlTable;
use Fyre\DB\Schema\SchemaRegistry;
use Fyre\DB\TypeParser;
use Override;
use PHPUnit\Framework\TestCase;
use Tests\Mock\DB\TestMysqlConnection;
use Tests\Mock\DB\TestSchema;

final class QueryGeneratorTest extends TestCase
{
    use QueryGeneratorTestTrait;

    protected MysqlForge $forge;

    protected MysqlQueryGenerator $generator;

    protected MysqlTable $table;

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(TypeParser::class);
        $container->singleton(SchemaRegistry::class);

        $db = $container->build(TestMysqlConnection::class);

        $container->use(SchemaRegistry::class)
            ->map(TestMysqlConnection::class, TestSchema::class);

        $forge = $container->use(ForgeRegistry::class)->use($db);

        $this->assertInstanceOf(MysqlForge::class, $forge);

        $this->forge = $forge;
        $this->generator = $forge->generator();
        $this->table = $forge->build('test', [
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);
    }
}
