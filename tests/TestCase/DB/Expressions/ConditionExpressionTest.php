<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Expressions;

use Fyre\DB\Expressions\ConditionExpression;
use Fyre\DB\Expressions\IdentifierExpression;
use Fyre\DB\Expressions\LiteralExpression;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ConditionExpressionTest extends TestCase
{
    public function testAnd(): void
    {
        $nested = new ConditionExpression();
        $source = new ConditionExpression('OR');
        $condition = $source->and($nested);

        $this->assertSame('AND', $condition->getConjunction());
        $this->assertSame([$nested], $condition->getConditions());
        $this->assertSame('OR', $source->getConjunction());
        $this->assertSame([], $source->getConditions());
    }

    public function testConjunctionInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Condition expression conjunction `INVALID` is not valid.');

        new ConditionExpression('invalid');
    }

    public function testIdentifierExpression(): void
    {
        $field = new IdentifierExpression('test.id');
        $expression = new ConditionExpression();
        $expression->eq('test.id', 1);

        $this->assertEquals(
            [
                [
                    'field' => $field,
                    'operator' => '=',
                    'value' => 1,
                ],
            ],
            $expression->getConditions()
        );
    }

    public function testInEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Condition expression IN values must not be empty.');

        $expression = new ConditionExpression();
        $expression->in('id', []);
    }

    public function testLiteralExpression(): void
    {
        $field = new LiteralExpression('LOWER(name)');
        $expression = new ConditionExpression();
        $expression->eq($field, 'test');

        $this->assertSame(
            [
                [
                    'field' => $field,
                    'operator' => '=',
                    'value' => 'test',
                ],
            ],
            $expression->getConditions()
        );
    }

    public function testNotInEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Condition expression NOT IN values must not be empty.');

        $expression = new ConditionExpression();
        $expression->notIn('id', []);
    }

    public function testOr(): void
    {
        $nested = new ConditionExpression();
        $source = new ConditionExpression();
        $condition = $source->or($nested);

        $this->assertSame('OR', $condition->getConjunction());
        $this->assertSame([$nested], $condition->getConditions());
        $this->assertSame('AND', $source->getConjunction());
        $this->assertSame([], $source->getConditions());
    }
}
