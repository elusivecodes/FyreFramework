<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Expressions;

use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Expressions\CaseExpression;
use Fyre\DB\Expressions\FunctionBuilder;
use Fyre\DB\Expressions\LiteralExpression;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class FunctionBuilderTest extends TestCase
{
    public function testAbs(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->abs('value');

        $this->assertSame(
            'ABS',
            $function->getName()
        );
    }

    public function testAvg(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->avg('value');

        $this->assertSame(
            'AVG',
            $function->getName()
        );
    }

    public function testCastInvalidDataType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query function data type `CHAR); DROP TABLE test` is not valid.');

        $builder = new FunctionBuilder();
        $builder->cast('value', 'CHAR); DROP TABLE test');
    }

    public function testCeil(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->ceil('value');

        $this->assertSame(
            'CEIL',
            $function->getName()
        );
    }

    public function testCoalesceEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query function COALESCE requires at least one argument.');

        $builder = new FunctionBuilder();
        $builder->coalesce([]);
    }

    public function testConcatEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query function CONCAT requires at least one argument.');

        $builder = new FunctionBuilder();
        $builder->concat([]);
    }

    public function testCount(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->count();

        $this->assertSame(
            'COUNT',
            $function->getName()
        );
    }

    public function testCountLiteralExpression(): void
    {
        $literal = new LiteralExpression('DISTINCT test.id');
        $builder = new FunctionBuilder();
        $function = $builder->count($literal);

        $this->assertSame(
            [$literal],
            $function->getArguments()
        );
    }

    public function testCumeDist(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->cumeDist()->getFunction();

        $this->assertSame(
            'CUME_DIST',
            $function->getName()
        );
    }

    public function testDateAddInvalidUnit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query function DATE_ADD unit `invalid` is not valid.');

        $builder = new FunctionBuilder();
        $builder->dateAdd('created', 1, 'invalid');
    }

    public function testDateDiffInvalidArguments(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query function DATE_DIFF requires two arguments.');

        $builder = new FunctionBuilder();
        $builder->dateDiff(['created']);
    }

    public function testDatePartInvalidPart(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query function DATE_PART part `invalid` is not valid.');

        $builder = new FunctionBuilder();
        $builder->datePart('invalid', 'created');
    }

    public function testDateSubInvalidUnit(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query function DATE_SUB unit `invalid` is not valid.');

        $builder = new FunctionBuilder();
        $builder->dateSub('created', 1, 'invalid');
    }

    public function testDenseRank(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->denseRank()->getFunction();

        $this->assertSame(
            'DENSE_RANK',
            $function->getName()
        );
    }

    public function testExtractInvalidPart(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query function EXTRACT part `invalid` is not valid.');

        $builder = new FunctionBuilder();
        $builder->extract('invalid', 'created');
    }

    public function testFirstValue(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->firstValue('value')->getFunction();

        $this->assertSame(
            'FIRST_VALUE',
            $function->getName()
        );
    }

    public function testFloor(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->floor('value');

        $this->assertSame(
            'FLOOR',
            $function->getName()
        );
    }

    public function testLagInvalidOffset(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query function LAG offset must not be negative.');

        $builder = new FunctionBuilder();
        $builder->lag('value', -1);
    }

    public function testLastValue(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->lastValue('value')->getFunction();

        $this->assertSame(
            'LAST_VALUE',
            $function->getName()
        );
    }

    public function testLeadInvalidOffset(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query function LEAD offset must not be negative.');

        $builder = new FunctionBuilder();
        $builder->lead('value', -1);
    }

    public function testLength(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->length('value');

        $this->assertSame(
            'LENGTH',
            $function->getName()
        );
    }

    public function testLower(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->lower('value');

        $this->assertSame(
            'LOWER',
            $function->getName()
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(FunctionBuilder::class)
        );
    }

    public function testMax(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->max('value');

        $this->assertSame(
            'MAX',
            $function->getName()
        );
    }

    public function testMin(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->min('value');

        $this->assertSame(
            'MIN',
            $function->getName()
        );
    }

    public function testNowInvalidType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query function NOW type `invalid` is not valid.');

        $builder = new FunctionBuilder();
        $builder->now('invalid');
    }

    public function testNthValue(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->nthValue('value', 2)->getFunction();

        $this->assertSame(
            'NTH_VALUE',
            $function->getName()
        );
    }

    public function testNthValueInvalidOffset(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query function NTH_VALUE offset must be greater than zero.');

        $builder = new FunctionBuilder();
        $builder->nthValue('value', 0);
    }

    public function testNtile(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->ntile(4)->getFunction();

        $this->assertSame(
            'NTILE',
            $function->getName()
        );
    }

    public function testNtileInvalidBuckets(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query function NTILE buckets must be greater than zero.');

        $builder = new FunctionBuilder();
        $builder->ntile(0);
    }

    public function testNullIf(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->nullIf('value', null);

        $this->assertSame(
            'NULLIF',
            $function->getName()
        );
    }

    public function testPercentRank(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->percentRank()->getFunction();

        $this->assertSame(
            'PERCENT_RANK',
            $function->getName()
        );
    }

    public function testRank(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->rank()->getFunction();

        $this->assertSame(
            'RANK',
            $function->getName()
        );
    }

    public function testReplace(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->replace('value', 'a', 'b');

        $this->assertSame(
            'REPLACE',
            $function->getName()
        );
    }

    public function testRound(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->round('value');

        $this->assertSame(
            'ROUND',
            $function->getName()
        );
    }

    public function testSubstring(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->substring('value', 1);

        $this->assertSame(
            'SUBSTRING',
            $function->getName()
        );
    }

    public function testSubstringInvalidLength(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query function SUBSTRING length must not be negative.');

        $builder = new FunctionBuilder();
        $builder->substring('value', 1, -1);
    }

    public function testSubstringInvalidStart(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Query function SUBSTRING start must be greater than zero.');

        $builder = new FunctionBuilder();
        $builder->substring('value', 0);
    }

    public function testSum(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->sum('value');

        $this->assertSame(
            'SUM',
            $function->getName()
        );
    }

    public function testSumCaseExpression(): void
    {
        $case = new CaseExpression();
        $builder = new FunctionBuilder();
        $function = $builder->sum($case);

        $this->assertSame(
            [$case],
            $function->getArguments()
        );
    }

    public function testTrim(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->trim('value');

        $this->assertSame(
            'TRIM',
            $function->getName()
        );
    }

    public function testUpper(): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->upper('value');

        $this->assertSame(
            'UPPER',
            $function->getName()
        );
    }
}
