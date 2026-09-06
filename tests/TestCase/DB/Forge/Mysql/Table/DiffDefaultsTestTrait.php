<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Mysql\Table;

use Fyre\DB\Types\BinaryType;
use Fyre\DB\Types\BooleanType;
use Fyre\DB\Types\DateTimeType;
use Fyre\DB\Types\DateType;
use Fyre\DB\Types\DecimalType;
use Fyre\DB\Types\FloatType;
use Fyre\DB\Types\IntegerType;
use Fyre\DB\Types\StringType;
use Fyre\DB\Types\TextType;
use Fyre\DB\Types\TimeType;
use PHPUnit\Framework\Attributes\DataProvider;

trait DiffDefaultsTestTrait
{
    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function tableDiffDefaultsProvider(): array
    {
        return [
            'big int' => [['type' => IntegerType::class, 'precision' => 20]],
            'binary' => [['type' => BinaryType::class]],
            'boolean' => [['type' => BooleanType::class]],
            'char' => [['type' => StringType::class, 'length' => 1]],
            'date' => [['type' => DateType::class]],
            'datetime' => [['type' => DateTimeType::class]],
            'decimal' => [['type' => DecimalType::class]],
            'float' => [['type' => FloatType::class]],
            'int' => [['type' => IntegerType::class]],
            'long text' => [['type' => TextType::class, 'length' => 4294967295]],
            'medium int' => [['type' => IntegerType::class, 'precision' => 8]],
            'medium text' => [['type' => TextType::class, 'length' => 16777215]],
            'small int' => [['type' => IntegerType::class, 'precision' => 6]],
            'text' => [['type' => TextType::class]],
            'time' => [['type' => TimeType::class]],
            'timestamp' => [['type' => 'timestamp']],
            'tiny int' => [['type' => IntegerType::class, 'precision' => 4]],
            'tiny text' => [['type' => TextType::class, 'length' => 255]],
            'varchar' => [['type' => StringType::class]],
        ];
    }

    /**
     * @param array<string, mixed> $options
     */
    #[DataProvider('tableDiffDefaultsProvider')]
    public function testTableDiffDefaults(array $options): void
    {
        $this->forge->createTable('test', [
            'value' => $options,
        ]);

        $this->assertArraysAreIdentical(
            [],
            $this->forge
                ->build('test')
                ->clear()
                ->addColumn('value', $options)
                ->sql()
        );
    }
}
