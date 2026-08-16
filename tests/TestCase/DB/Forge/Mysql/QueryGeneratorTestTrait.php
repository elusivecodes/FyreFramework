<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Mysql;

use Fyre\DB\Types\BinaryType;
use Fyre\DB\Types\BooleanType;
use Fyre\DB\Types\DateTimeFractionalType;
use Fyre\DB\Types\DateType;
use Fyre\DB\Types\EnumType;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\TextType;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use stdClass;

trait QueryGeneratorTestTrait
{
    /**
     * @return array<string, mixed[]>
     */
    public static function columnProvider(): array
    {
        return [
            'tiny binary' => [[
                'type' => BinaryType::class,
                'length' => 255,
            ], '`value` TINYBLOB NOT NULL'],
            'medium binary' => [[
                'type' => BinaryType::class,
                'length' => 65_536,
            ], '`value` MEDIUMBLOB NOT NULL'],
            'long binary' => [[
                'type' => BinaryType::class,
                'length' => 16_777_216,
            ], '`value` LONGBLOB NOT NULL'],
            'fractional datetime' => [[
                'type' => DateTimeFractionalType::class,
                'fractionalSeconds' => 3,
            ], '`value` DATETIME(3) NOT NULL'],
            'boolean default' => [[
                'type' => BooleanType::class,
                'default' => false,
            ], '`value` TINYINT(1) NOT NULL DEFAULT 0'],
            'string default' => [[
                'type' => DateType::class,
                'default' => '2022-01-01',
            ], '`value` DATE NOT NULL DEFAULT \'2022-01-01\''],
            'text default' => [[
                'type' => TextType::class,
                'default' => 'Test',
            ], '`value` TEXT CHARACTER SET \'utf8mb4\' COLLATE \'utf8mb4_unicode_ci\' NOT NULL DEFAULT (\'Test\')'],
            'float default' => [[
                'type' => FloatType::class,
                'default' => '1.5',
            ], '`value` FLOAT NOT NULL DEFAULT 1.5'],
            'bit precision' => [[
                'type' => 'bit',
            ], '`value` BIT(1) NOT NULL'],
        ];
    }

    /**
     * @param array<string, mixed> $options
     */
    #[DataProvider('columnProvider')]
    public function testBuildColumn(array $options, string $expected): void
    {
        $column = $this->table
            ->addColumn('value', $options)
            ->column('value');

        $this->assertSame(
            $expected,
            $this->generator->buildColumn($column)
        );
    }

    public function testBuildCreateSchemaIfNotExists(): void
    {
        $this->assertSame(
            'CREATE SCHEMA IF NOT EXISTS `other` CHARACTER SET = \'utf8mb4\' COLLATE = \'utf8mb4_unicode_ci\'',
            $this->generator->buildCreateSchema('other', [
                'ifNotExists' => true,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ])
        );
    }

    public function testBuildCreateTableIfNotExists(): void
    {
        $this->table->addColumn('id', [
            'type' => IntegerType::class,
        ]);

        $this->assertSame(
            'CREATE TABLE IF NOT EXISTS `test` (`id` INT(11) NOT NULL) ENGINE = InnoDB DEFAULT CHARSET = \'utf8mb4\' COLLATE = \'utf8mb4_unicode_ci\'',
            $this->generator->buildCreateTable($this->table, [
                'ifNotExists' => true,
            ])
        );
    }

    public function testBuildDropColumnIfExists(): void
    {
        $this->assertSame(
            'DROP COLUMN IF EXISTS `value`',
            $this->generator->buildDropColumn('value', [
                'ifExists' => true,
            ])
        );
    }

    public function testBuildDropSchemaIfExists(): void
    {
        $this->assertSame(
            'DROP SCHEMA IF EXISTS `other`',
            $this->generator->buildDropSchema('other', [
                'ifExists' => true,
            ])
        );
    }

    public function testBuildDropTableIfExists(): void
    {
        $this->assertSame(
            'DROP TABLE IF EXISTS `test`',
            $this->generator->buildDropTable('test', [
                'ifExists' => true,
            ])
        );
    }

    public function testBuildIndexInvalidPrimaryType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Index type `fulltext` is not valid.');

        $index = $this->table
            ->addIndex('PRIMARY', [
                'columns' => 'id',
                'type' => 'fulltext',
            ])
            ->index('PRIMARY');

        $this->generator->buildIndex($index);
    }

    public function testBuildInvalidEnum(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Enum class `stdClass` must implement `UnitEnum`.');

        $column = $this->table
            ->addColumn('value', [
                'type' => EnumType::class,
                'values' => stdClass::class,
            ])
            ->column('value');

        $this->generator->buildColumn($column);
    }
}
