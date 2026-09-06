<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Forge\Postgres\Table;

use Fyre\DB\Types\BinaryType;
use Fyre\DB\Types\BooleanType;
use Fyre\DB\Types\DateTimeFractionalType;
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
            'boolean' => [['type' => BooleanType::class]],
            'bytea' => [['type' => BinaryType::class]],
            'character' => [['type' => StringType::class, 'length' => 1]],
            'character varying' => [['type' => StringType::class]],
            'date' => [['type' => DateType::class]],
            'integer' => [['type' => IntegerType::class]],
            'numeric' => [['type' => DecimalType::class]],
            'real' => [['type' => FloatType::class]],
            'small int' => [['type' => IntegerType::class, 'precision' => 6]],
            'text' => [['type' => TextType::class]],
            'time' => [['type' => TimeType::class]],
            'timestamp' => [['type' => DateTimeFractionalType::class]],
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
