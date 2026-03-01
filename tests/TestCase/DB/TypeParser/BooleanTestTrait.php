<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

trait BooleanTestTrait
{
    public function testBooleanFromDatabase(): void
    {
        $this->assertTrue(
            $this->type->use('boolean')->fromDatabase('1')
        );
    }

    public function testBooleanFromDatabaseFalse(): void
    {
        $this->assertFalse(
            $this->type->use('boolean')->fromDatabase(false)
        );
    }

    public function testBooleanFromDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('boolean')->fromDatabase(null)
        );
    }

    public function testBooleanFromDatabaseTrue(): void
    {
        $this->assertTrue(
            $this->type->use('boolean')->fromDatabase(true)
        );
    }

    public function testBooleanFromDatabaseZero(): void
    {
        $this->assertFalse(
            $this->type->use('boolean')->fromDatabase('0')
        );
    }

    public function testBooleanParse(): void
    {
        $this->assertTrue(
            $this->type->use('boolean')->parse('1')
        );
    }

    public function testBooleanParseFalse(): void
    {
        $this->assertFalse(
            $this->type->use('boolean')->parse(false)
        );
    }

    public function testBooleanParseInvalid(): void
    {
        $this->assertNull(
            $this->type->use('boolean')->parse('invalid')
        );
    }

    public function testBooleanParseNull(): void
    {
        $this->assertNull(
            $this->type->use('boolean')->parse(null)
        );
    }

    public function testBooleanParseTrue(): void
    {
        $this->assertTrue(
            $this->type->use('boolean')->parse(true)
        );
    }

    public function testBooleanParseZero(): void
    {
        $this->assertFalse(
            $this->type->use('boolean')->parse('0')
        );
    }

    public function testBooleanToDatabase(): void
    {
        $this->assertTrue(
            $this->type->use('boolean')->toDatabase('1')
        );
    }

    public function testBooleanToDatabaseFalse(): void
    {
        $this->assertFalse(
            $this->type->use('boolean')->toDatabase(false)
        );
    }

    public function testBooleanToDatabaseInvalid(): void
    {
        $this->assertNull(
            $this->type->use('boolean')->toDatabase('invalid')
        );
    }

    public function testBooleanToDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('boolean')->toDatabase(null)
        );
    }

    public function testBooleanToDatabaseTrue(): void
    {
        $this->assertTrue(
            $this->type->use('boolean')->toDatabase(true)
        );
    }

    public function testBooleanToDatabaseZero(): void
    {
        $this->assertFalse(
            $this->type->use('boolean')->toDatabase('0')
        );
    }
}
