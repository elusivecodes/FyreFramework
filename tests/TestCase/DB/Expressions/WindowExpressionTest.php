<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Expressions;

use Fyre\DB\Expressions\FunctionExpression;
use Fyre\DB\Expressions\IdentifierExpression;
use Fyre\DB\Expressions\LiteralExpression;
use Fyre\DB\Expressions\WindowExpression;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class WindowExpressionTest extends TestCase
{
    public function testExcludeCurrent(): void
    {
        $function = new FunctionExpression('ROW_NUMBER');
        $window = new WindowExpression($function);

        $this->assertSame(
            $window,
            $window->excludeCurrent()
        );
        $this->assertSame(
            'CURRENT ROW',
            $window->getExclude()
        );
    }

    public function testExcludeGroup(): void
    {
        $function = new FunctionExpression('ROW_NUMBER');
        $window = new WindowExpression($function);

        $this->assertSame(
            $window,
            $window->excludeGroup()
        );
        $this->assertSame(
            'GROUP',
            $window->getExclude()
        );
    }

    public function testExcludeTies(): void
    {
        $function = new FunctionExpression('ROW_NUMBER');
        $window = new WindowExpression($function);

        $this->assertSame(
            $window,
            $window->excludeTies()
        );
        $this->assertSame(
            'TIES',
            $window->getExclude()
        );
    }

    public function testGroups(): void
    {
        $function = new FunctionExpression('ROW_NUMBER');
        $window = new WindowExpression($function);

        $this->assertSame(
            $window,
            $window->groups(null, null)
        );
        $frame = $window->getFrame();

        $this->assertIsArray($frame);
        $this->assertArraysAreIdentical(
            [
                'type' => 'GROUPS',
                'start' => 'UNBOUNDED PRECEDING',
                'end' => 'UNBOUNDED FOLLOWING',
            ],
            $frame
        );
    }

    public function testInvalidFrameOffset(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Query window frame offset must not be negative.');

        $function = new FunctionExpression('ROW_NUMBER');
        $window = new WindowExpression($function);
        $window->rows(-1);
    }

    public function testOrderBy(): void
    {
        $function = new FunctionExpression('ROW_NUMBER');
        $window = new WindowExpression($function);

        $this->assertSame(
            $window,
            $window->orderBy('test.id')
        );
        $this->assertSame(
            $window,
            $window->orderBy([
                'test.created' => 'DESC',
            ])
        );
        $this->assertArraysAreIdentical(
            [
                'test.id',
                'test.created' => 'DESC',
            ],
            $window->getOrderBy()
        );

        $window->orderBy('test.name', true);

        $this->assertArraysAreIdentical(
            ['test.name'],
            $window->getOrderBy()
        );
    }

    public function testPartitionBy(): void
    {
        $function = new FunctionExpression('ROW_NUMBER');
        $groupField = new IdentifierExpression('test.group_id');
        $field = new IdentifierExpression('test.id');
        $literal = new LiteralExpression('DATE(test.created)');
        $window = new WindowExpression($function);

        $this->assertSame(
            $window,
            $window->partitionBy('test.group_id')
        );
        $this->assertSame(
            $window,
            $window->partitionBy($field)
        );
        $this->assertSame(
            $window,
            $window->partitionBy($literal)
        );
        $this->assertEquals(
            [
                $groupField,
                $field,
                $literal,
            ],
            $window->getPartitionBy()
        );

        $accountField = new IdentifierExpression('test.account_id');
        $window->partitionBy('test.account_id', true);

        $this->assertEquals(
            [$accountField],
            $window->getPartitionBy()
        );
    }

    public function testRange(): void
    {
        $function = new FunctionExpression('ROW_NUMBER');
        $window = new WindowExpression($function);

        $this->assertSame(
            $window,
            $window->range(null)
        );
        $frame = $window->getFrame();

        $this->assertIsArray($frame);
        $this->assertArraysAreIdentical(
            [
                'type' => 'RANGE',
                'start' => 'UNBOUNDED PRECEDING',
                'end' => 'CURRENT ROW',
            ],
            $frame
        );
    }

    public function testRows(): void
    {
        $function = new FunctionExpression('ROW_NUMBER');
        $window = new WindowExpression($function);

        $this->assertSame(
            $window,
            $window->rows(5, 2)
        );
        $frame = $window->getFrame();

        $this->assertIsArray($frame);
        $this->assertArraysAreIdentical(
            [
                'type' => 'ROWS',
                'start' => '5 PRECEDING',
                'end' => '2 FOLLOWING',
            ],
            $frame
        );
    }
}
