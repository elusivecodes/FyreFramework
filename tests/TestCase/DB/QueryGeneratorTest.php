<?php
declare(strict_types=1);

namespace Tests\TestCase\DB;

use Fyre\DB\QueryGenerator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class QueryGeneratorTest extends TestCase
{
    public function testCombineConditionsValueCountInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Condition fields and values must contain the same number of elements.');

        QueryGenerator::combineConditions(['id', 'type'], [1]);
    }

    public function testNormalizeConditionsValueCountInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Condition fields and values must contain the same number of elements.');

        QueryGenerator::normalizeConditions(['id'], [
            [1],
            [2, 3],
        ]);
    }
}
