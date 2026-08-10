<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Expressions;

use Fyre\DB\Expressions\AggregateExpression;
use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Expressions\IdentifierExpression;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class AggregateExpressionTest extends TestCase
{
    public function testDistinct(): void
    {
        $field = new IdentifierExpression('test.id');
        $aggregate = new AggregateExpression('COUNT', $field);

        $this->assertSame(
            $aggregate,
            $aggregate->distinct()
        );
        $this->assertTrue($aggregate->getDistinct());

        $aggregate->distinct(false);

        $this->assertFalse($aggregate->getDistinct());
    }

    public function testDistinctCountAll(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query aggregate COUNT(*) cannot use distinct values.');

        $field = new IdentifierExpression('*');
        $aggregate = new AggregateExpression('COUNT', $field);
        $aggregate->distinct();
    }

    public function testFilter(): void
    {
        $field = new IdentifierExpression('test.total');
        $condition = new ConditionExpression();
        $aggregate = new AggregateExpression('SUM', $field);

        $this->assertSame(
            $aggregate,
            $aggregate->filter($condition)
        );
        $this->assertSame(
            $condition,
            $aggregate->getFilter()
        );
    }

    public function testOver(): void
    {
        $field = new IdentifierExpression('test.total');
        $aggregate = new AggregateExpression('SUM', $field);
        $window = $aggregate->over();

        $this->assertSame(
            $aggregate,
            $window->getFunction()
        );
    }
}
