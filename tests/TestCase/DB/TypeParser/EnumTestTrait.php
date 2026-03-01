<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

use stdClass;

trait EnumTestTrait
{
    public function testEnumFromDatabase(): void
    {
        $this->assertSame(
            'test',
            $this->type->use('enum')->fromDatabase('test')
        );
    }

    public function testEnumFromDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('enum')->fromDatabase(null)
        );
    }

    public function testEnumParse(): void
    {
        $this->assertSame(
            'test',
            $this->type->use('enum')->parse('test')
        );
    }

    public function testEnumParseInvalid(): void
    {
        $this->assertNull(
            $this->type->use('enum')->parse(new stdClass())
        );
    }

    public function testEnumParseNull(): void
    {
        $this->assertNull(
            $this->type->use('enum')->parse(null)
        );
    }

    public function testEnumToDatabase(): void
    {
        $this->assertSame(
            'test',
            $this->type->use('enum')->toDatabase('test')
        );
    }

    public function testEnumToDatabaseInvalid(): void
    {
        $obj = new stdClass();

        $this->assertNull(
            $this->type->use('enum')->toDatabase(new stdClass())
        );
    }

    public function testEnumToDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('enum')->toDatabase(null)
        );
    }
}
