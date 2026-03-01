<?php
declare(strict_types=1);

namespace Tests\TestCase\Form;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\DB\TypeParser;
use Fyre\Form\Field;
use Fyre\Form\Schema;
use Override;
use PHPUnit\Framework\TestCase;

use function array_map;
use function class_uses;

final class SchemaTest extends TestCase
{
    protected Schema $schema;

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Schema::class)
        );
    }

    public function testField(): void
    {
        $field = $this->schema->field('title');

        $this->assertInstanceOf(Field::class, $field);

        $this->assertSame(
            'title',
            $field->getName()
        );

        $this->assertSame(
            'string',
            $field->getType()
        );

        $this->assertNull(
            $field->getLength()
        );

        $this->assertNull(
            $field->getPrecision()
        );

        $this->assertNull(
            $field->getScale()
        );

        $this->assertNull(
            $field->getFractionalSeconds()
        );

        $this->assertNull(
            $field->getDefault()
        );
    }

    public function testFieldNames(): void
    {
        $this->assertSame(
            [
                'title',
                'user_id',
            ],
            $this->schema->fieldNames()
        );
    }

    public function testFields(): void
    {
        $fields = $this->schema->fields();

        $this->assertSame(
            [
                'title' => [
                    'name' => 'title',
                    'type' => 'string',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'default' => null,
                ],
                'user_id' => [
                    'name' => 'user_id',
                    'type' => 'integer',
                    'length' => null,
                    'precision' => null,
                    'scale' => null,
                    'fractionalSeconds' => null,
                    'default' => null,
                ],
            ],
            array_map(
                fn(Field $field): array => $field->toArray(),
                $fields
            )
        );
    }

    public function testHasField(): void
    {
        $this->assertTrue(
            $this->schema->hasField('title')
        );
    }

    public function testHasFieldFalse(): void
    {
        $this->assertFalse(
            $this->schema->hasField('invalid')
        );
    }

    public function testRemoveField(): void
    {
        $this->assertSame(
            $this->schema,
            $this->schema->removeField('title')
        );

        $this->assertFalse(
            $this->schema->hasField('title')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(TypeParser::class);

        $this->schema = $container->build(Schema::class);

        $this->schema->addField('title', [
            'type' => 'string',
        ]);
        $this->schema->addField('user_id', [
            'type' => 'integer',
        ]);
    }
}
