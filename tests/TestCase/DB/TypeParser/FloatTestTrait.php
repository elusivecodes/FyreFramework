<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

trait FloatTestTrait
{
    public function testFloatFromDatabase(): void
    {
        $this->assertSame(
            33.3,
            $this->type->use('float')->fromDatabase('33.3')
        );
    }

    public function testFloatFromDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('float')->fromDatabase(null)
        );
    }

    public function testFloatParse(): void
    {
        $this->assertSame(
            33.3,
            $this->type->use('float')->parse('33.3')
        );
    }

    public function testFloatParseInvalid(): void
    {
        $this->assertNull(
            $this->type->use('float')->parse('invalid')
        );
    }

    public function testFloatParseNull(): void
    {
        $this->assertNull(
            $this->type->use('float')->parse(null)
        );
    }

    public function testFloatToDatabase(): void
    {
        $this->assertSame(
            33.3,
            $this->type->use('float')->toDatabase('33.3')
        );
    }

    public function testFloatToDatabaseInvalid(): void
    {
        $this->assertNull(
            $this->type->use('float')->toDatabase('invalid')
        );
    }

    public function testFloatToDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('float')->toDatabase(null)
        );
    }
}
