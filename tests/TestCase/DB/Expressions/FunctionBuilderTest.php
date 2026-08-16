<?php
declare(strict_types=1);

namespace Tests\TestCase\DB\Expressions;

use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\Expressions\CaseExpression;
use Fyre\DB\Expressions\FunctionBuilder;
use Fyre\DB\Expressions\LiteralExpression;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function class_uses;

/**
 * @phpstan-type FunctionMethod 'abs'|'avg'|'ceil'|'count'|'floor'|'length'|'lower'|'max'|'min'|'nullIf'|'replace'|'round'|'substring'|'sum'|'trim'|'upper'
 * @phpstan-type InvalidMethod 'cast'|'coalesce'|'concat'|'dateAdd'|'dateDiff'|'datePart'|'dateSub'|'extract'|'lag'|'lead'|'now'|'nthValue'|'ntile'|'substring'
 * @phpstan-type WindowFunctionMethod 'cumeDist'|'denseRank'|'firstValue'|'lastValue'|'nthValue'|'ntile'|'percentRank'|'rank'
 */
final class FunctionBuilderTest extends TestCase
{
    /**
     * @return array<string, array{string, mixed[], string}>
     */
    public static function functionNameProvider(): array
    {
        return [
            'absolute value' => ['abs', ['value'], 'ABS'],
            'average' => ['avg', ['value'], 'AVG'],
            'ceiling' => ['ceil', ['value'], 'CEIL'],
            'count' => ['count', [], 'COUNT'],
            'floor' => ['floor', ['value'], 'FLOOR'],
            'length' => ['length', ['value'], 'LENGTH'],
            'lowercase' => ['lower', ['value'], 'LOWER'],
            'maximum' => ['max', ['value'], 'MAX'],
            'minimum' => ['min', ['value'], 'MIN'],
            'null if' => ['nullIf', ['value', null], 'NULLIF'],
            'replace' => ['replace', ['value', 'a', 'b'], 'REPLACE'],
            'round' => ['round', ['value'], 'ROUND'],
            'substring' => ['substring', ['value', 1], 'SUBSTRING'],
            'sum' => ['sum', ['value'], 'SUM'],
            'trim' => ['trim', ['value'], 'TRIM'],
            'uppercase' => ['upper', ['value'], 'UPPER'],
        ];
    }

    /**
     * @return array<string, array{string, mixed[], string}>
     */
    public static function invalidProvider(): array
    {
        return [
            'cast data type' => [
                'cast',
                ['value', 'CHAR); DROP TABLE test'],
                'Query function data type `CHAR); DROP TABLE test` is not valid.',
            ],
            'coalesce arguments' => ['coalesce', [[]], 'Query function COALESCE requires at least one argument.'],
            'concat arguments' => ['concat', [[]], 'Query function CONCAT requires at least one argument.'],
            'date add unit' => ['dateAdd', ['created', 1, 'invalid'], 'Query function DATE_ADD unit `invalid` is not valid.'],
            'date diff arguments' => ['dateDiff', [['created']], 'Query function DATE_DIFF requires two arguments.'],
            'date part' => ['datePart', ['invalid', 'created'], 'Query function DATE_PART part `invalid` is not valid.'],
            'date sub unit' => ['dateSub', ['created', 1, 'invalid'], 'Query function DATE_SUB unit `invalid` is not valid.'],
            'extract part' => ['extract', ['invalid', 'created'], 'Query function EXTRACT part `invalid` is not valid.'],
            'lag offset' => ['lag', ['value', -1], 'Query function LAG offset must not be negative.'],
            'lead offset' => ['lead', ['value', -1], 'Query function LEAD offset must not be negative.'],
            'now type' => ['now', ['invalid'], 'Query function NOW type `invalid` is not valid.'],
            'nth value offset' => ['nthValue', ['value', 0], 'Query function NTH_VALUE offset must be greater than zero.'],
            'ntile buckets' => ['ntile', [0], 'Query function NTILE buckets must be greater than zero.'],
            'substring length' => ['substring', ['value', 1, -1], 'Query function SUBSTRING length must not be negative.'],
            'substring start' => ['substring', ['value', 0], 'Query function SUBSTRING start must be greater than zero.'],
        ];
    }

    /**
     * @return array<string, array{string, mixed[], string}>
     */
    public static function windowFunctionNameProvider(): array
    {
        return [
            'cumulative distribution' => ['cumeDist', [], 'CUME_DIST'],
            'dense rank' => ['denseRank', [], 'DENSE_RANK'],
            'first value' => ['firstValue', ['value'], 'FIRST_VALUE'],
            'last value' => ['lastValue', ['value'], 'LAST_VALUE'],
            'nth value' => ['nthValue', ['value', 2], 'NTH_VALUE'],
            'ntile' => ['ntile', [4], 'NTILE'],
            'percent rank' => ['percentRank', [], 'PERCENT_RANK'],
            'rank' => ['rank', [], 'RANK'],
        ];
    }

    public function testCountLiteralExpression(): void
    {
        $literal = new LiteralExpression('DISTINCT test.id');
        $builder = new FunctionBuilder();
        $function = $builder->count($literal);

        $this->assertArraysAreIdentical(
            [$literal],
            $function->getArguments()
        );
    }

    /**
     * @param FunctionMethod $method
     * @param mixed[] $arguments
     */
    #[DataProvider('functionNameProvider')]
    public function testFunctionName(string $method, array $arguments, string $expected): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->$method(...$arguments);

        $this->assertSame(
            $expected,
            $function->getName()
        );
    }

    /**
     * @param InvalidMethod $method
     * @param mixed[] $arguments
     */
    #[DataProvider('invalidProvider')]
    public function testInvalid(string $method, array $arguments, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        $builder = new FunctionBuilder();
        $builder->$method(...$arguments);
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(FunctionBuilder::class)
        );
    }

    public function testSumCaseExpression(): void
    {
        $case = new CaseExpression();
        $builder = new FunctionBuilder();
        $function = $builder->sum($case);

        $this->assertArraysAreIdentical(
            [$case],
            $function->getArguments()
        );
    }

    /**
     * @param WindowFunctionMethod $method
     * @param mixed[] $arguments
     */
    #[DataProvider('windowFunctionNameProvider')]
    public function testWindowFunctionName(string $method, array $arguments, string $expected): void
    {
        $builder = new FunctionBuilder();
        $function = $builder->$method(...$arguments)->getFunction();

        $this->assertSame(
            $expected,
            $function->getName()
        );
    }
}
