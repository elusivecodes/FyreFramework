<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\TypeParser;

use stdClass;

trait SetTestTrait
{
    public function testSetFromDatabase(): void
    {
        $this->assertSame(
            ['a', 'b', 'c'],
            $this->type->use('set')->fromDatabase('a,b,c')
        );
    }

    public function testSetFromDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('set')->fromDatabase(null)
        );
    }

    public function testSetParse(): void
    {
        $this->assertSame(
            ['a', 'b', 'c'],
            $this->type->use('set')->parse('a,b,c')
        );
    }

    public function testSetParseArray(): void
    {
        $this->assertSame(
            ['a', 'b', 'c'],
            $this->type->use('set')->parse(['a', 'b', 'c'])
        );
    }

    public function testSetParseInvalid(): void
    {
        $this->assertNull(
            $this->type->use('set')->parse(new stdClass())
        );
    }

    public function testSetParseNull(): void
    {
        $this->assertNull(
            $this->type->use('set')->parse(null)
        );
    }

    public function testSetToDatabase(): void
    {
        $this->assertSame(
            'a,b,c',
            $this->type->use('set')->toDatabase(['a', 'b', 'c'])
        );
    }

    public function testSetToDatabaseInvalid(): void
    {
        $obj = new stdClass();

        $this->assertNull(
            $this->type->use('set')->toDatabase(new stdClass())
        );
    }

    public function testSetToDatabaseNull(): void
    {
        $this->assertNull(
            $this->type->use('set')->toDatabase(null)
        );
    }

    public function testSetToDatabaseString(): void
    {
        $this->assertSame(
            'a,b,c',
            $this->type->use('set')->toDatabase('a,b,c')
        );
    }
}
