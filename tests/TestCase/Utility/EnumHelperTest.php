<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility;

use Fyre\Utility\EnumHelper;
use PHPUnit\Framework\TestCase;
use Tests\Mock\Enums\State;
use Tests\Mock\Enums\Status;

final class EnumHelperTest extends TestCase
{
    public function testNormalizeValueBackedEnum(): void
    {
        $this->assertSame(
            'draft',
            EnumHelper::normalizeValue(Status::Draft)
        );
    }

    public function testNormalizeValueUnitEnum(): void
    {
        $this->assertSame(
            'Draft',
            EnumHelper::normalizeValue(State::Draft)
        );
    }

    public function testParseValueBackedEnum(): void
    {
        $this->assertSame(
            Status::Draft,
            EnumHelper::parseValue(Status::class, 'draft')
        );
    }

    public function testParseValueInvalid(): void
    {
        $this->assertNull(
            EnumHelper::parseValue(Status::class, 'invalid')
        );
    }

    public function testParseValueUnitEnum(): void
    {
        $this->assertSame(
            State::Draft,
            EnumHelper::parseValue(State::class, 'Draft')
        );
    }
}
