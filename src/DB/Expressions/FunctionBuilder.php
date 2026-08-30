<?php
declare(strict_types=1);

namespace Fyre\DB\Expressions;

use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use InvalidArgumentException;

use function count;
use function in_array;
use function is_string;
use function preg_match;
use function sprintf;
use function strtolower;

/**
 * Builds query functions.
 */
class FunctionBuilder
{
    use DebugTrait;
    use MacroTrait;

    protected const DATE_PARTS = [
        'day',
        'hour',
        'minute',
        'month',
        'second',
        'week',
        'year',
    ];

    /**
     * Builds an ABS function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function abs(string|ValueExpressionInterface $expression): FunctionExpression
    {
        return $this->valueFunction('ABS', $expression);
    }

    /**
     * Builds an AVG function.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @return AggregateExpression The new AggregateExpression instance.
     */
    public function avg(string|ValueExpressionInterface $field): AggregateExpression
    {
        return $this->aggregate('AVG', $field);
    }

    /**
     * Builds a CAST expression.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @param string $dataType The data type.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function cast(string|ValueExpressionInterface $field, string $dataType): FunctionExpression
    {
        if (!preg_match('/\A[a-z][a-z0-9_]*(?:\(\d+(?:\s*,\s*\d+)?\))?(?:\s+[a-z][a-z0-9_]*)*\z/i', $dataType)) {
            throw new InvalidArgumentException(sprintf(
                'Query function data type `%s` is not valid.',
                $dataType
            ));
        }

        return new FunctionExpression('CAST', [
            $this->valueExpression($field),
            $dataType,
        ]);
    }

    /**
     * Builds a CEIL function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function ceil(string|ValueExpressionInterface $expression): FunctionExpression
    {
        return $this->valueFunction('CEIL', $expression);
    }

    /**
     * Builds a COALESCE function.
     *
     * @param mixed[] $arguments The function arguments.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function coalesce(array $arguments): FunctionExpression
    {
        if ($arguments === []) {
            throw new InvalidArgumentException('Query function COALESCE requires at least one argument.');
        }

        return new FunctionExpression('COALESCE', $arguments);
    }

    /**
     * Builds a CONCAT function.
     *
     * @param mixed[] $arguments The function arguments.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function concat(array $arguments): FunctionExpression
    {
        if ($arguments === []) {
            throw new InvalidArgumentException('Query function CONCAT requires at least one argument.');
        }

        return new FunctionExpression('CONCAT', $arguments);
    }

    /**
     * Builds a COUNT function.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @return AggregateExpression The new AggregateExpression instance.
     */
    public function count(string|ValueExpressionInterface $field = '*'): AggregateExpression
    {
        return $this->aggregate('COUNT', $field);
    }

    /**
     * Builds a CUME_DIST function.
     *
     * @return WindowExpression The new WindowExpression instance.
     */
    public function cumeDist(): WindowExpression
    {
        return $this->windowFunction('CUME_DIST');
    }

    /**
     * Builds a date addition expression.
     *
     * @param string|ValueExpressionInterface $expression The date expression.
     * @param int|string $value The value to add.
     * @param string $unit The date unit.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function dateAdd(
        string|ValueExpressionInterface $expression,
        int|string $value,
        string $unit
    ): FunctionExpression {
        $unit = strtolower($unit);

        if (!in_array($unit, static::DATE_PARTS, true)) {
            throw new InvalidArgumentException(sprintf(
                'Query function DATE_ADD unit `%s` is not valid.',
                $unit
            ));
        }

        return new FunctionExpression('DATE_ADD', [
            $this->valueExpression($expression),
            $value,
            $unit,
        ]);
    }

    /**
     * Builds a date difference expression.
     *
     * @param mixed[] $arguments The date expressions.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function dateDiff(array $arguments): FunctionExpression
    {
        if (count($arguments) !== 2) {
            throw new InvalidArgumentException('Query function DATE_DIFF requires two arguments.');
        }

        return new FunctionExpression('DATE_DIFF', $arguments);
    }

    /**
     * Builds a date part expression.
     *
     * @param string $part The date part.
     * @param string|ValueExpressionInterface $expression The date expression.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function datePart(string $part, string|ValueExpressionInterface $expression): FunctionExpression
    {
        $part = strtolower($part);

        if (!in_array($part, static::DATE_PARTS, true)) {
            throw new InvalidArgumentException(sprintf(
                'Query function DATE_PART part `%s` is not valid.',
                $part
            ));
        }

        return new FunctionExpression('DATE_PART', [
            $part,
            $this->valueExpression($expression),
        ]);
    }

    /**
     * Builds a date subtraction expression.
     *
     * @param string|ValueExpressionInterface $expression The date expression.
     * @param int|string $value The value to subtract.
     * @param string $unit The date unit.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function dateSub(
        string|ValueExpressionInterface $expression,
        int|string $value,
        string $unit
    ): FunctionExpression {
        $unit = strtolower($unit);

        if (!in_array($unit, static::DATE_PARTS, true)) {
            throw new InvalidArgumentException(sprintf(
                'Query function DATE_SUB unit `%s` is not valid.',
                $unit
            ));
        }

        return new FunctionExpression('DATE_SUB', [
            $this->valueExpression($expression),
            $value,
            $unit,
        ]);
    }

    /**
     * Builds a day-of-week expression.
     *
     * @param string|ValueExpressionInterface $expression The date expression.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function dayOfWeek(string|ValueExpressionInterface $expression): FunctionExpression
    {
        return new FunctionExpression('DAY_OF_WEEK', [
            $this->valueExpression($expression),
        ]);
    }

    /**
     * Builds a DENSE_RANK function.
     *
     * @return WindowExpression The new WindowExpression instance.
     */
    public function denseRank(): WindowExpression
    {
        return $this->windowFunction('DENSE_RANK');
    }

    /**
     * Builds an EXTRACT expression.
     *
     * @param string $part The date part.
     * @param string|ValueExpressionInterface $expression The date expression.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function extract(string $part, string|ValueExpressionInterface $expression): FunctionExpression
    {
        $part = strtolower($part);

        if (!in_array($part, static::DATE_PARTS, true)) {
            throw new InvalidArgumentException(sprintf(
                'Query function EXTRACT part `%s` is not valid.',
                $part
            ));
        }

        return new FunctionExpression('EXTRACT', [
            $part,
            $this->valueExpression($expression),
        ]);
    }

    /**
     * Builds a FIRST_VALUE function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @return WindowExpression The new WindowExpression instance.
     */
    public function firstValue(string|ValueExpressionInterface $expression): WindowExpression
    {
        return $this->windowFunction('FIRST_VALUE', [
            $this->valueExpression($expression),
        ]);
    }

    /**
     * Builds a FLOOR function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function floor(string|ValueExpressionInterface $expression): FunctionExpression
    {
        return $this->valueFunction('FLOOR', $expression);
    }

    /**
     * Builds a JSON value expression.
     *
     * @param string|ValueExpressionInterface $expression The JSON expression.
     * @param string $path The JSON path.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function jsonValue(string|ValueExpressionInterface $expression, string $path): FunctionExpression
    {
        return new FunctionExpression('JSON_VALUE', [
            $this->valueExpression($expression),
            $path,
        ]);
    }

    /**
     * Builds a LAG function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @param int $offset The row offset.
     * @param mixed $default The default value.
     * @return WindowExpression The new WindowExpression instance.
     */
    public function lag(
        string|ValueExpressionInterface $expression,
        int $offset = 1,
        mixed $default = null
    ): WindowExpression {
        if ($offset < 0) {
            throw new InvalidArgumentException('Query function LAG offset must not be negative.');
        }

        return $this->windowFunction(
            'LAG',
            $this->windowOffsetArguments($expression, $offset, $default)
        );
    }

    /**
     * Builds a LAST_VALUE function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @return WindowExpression The new WindowExpression instance.
     */
    public function lastValue(string|ValueExpressionInterface $expression): WindowExpression
    {
        return $this->windowFunction('LAST_VALUE', [
            $this->valueExpression($expression),
        ]);
    }

    /**
     * Builds a LEAD function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @param int $offset The row offset.
     * @param mixed $default The default value.
     * @return WindowExpression The new WindowExpression instance.
     */
    public function lead(
        string|ValueExpressionInterface $expression,
        int $offset = 1,
        mixed $default = null
    ): WindowExpression {
        if ($offset < 0) {
            throw new InvalidArgumentException('Query function LEAD offset must not be negative.');
        }

        return $this->windowFunction(
            'LEAD',
            $this->windowOffsetArguments($expression, $offset, $default)
        );
    }

    /**
     * Builds a LENGTH function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function length(string|ValueExpressionInterface $expression): FunctionExpression
    {
        return $this->valueFunction('LENGTH', $expression);
    }

    /**
     * Builds a LOWER function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function lower(string|ValueExpressionInterface $expression): FunctionExpression
    {
        return $this->valueFunction('LOWER', $expression);
    }

    /**
     * Builds a MAX function.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @return AggregateExpression The new AggregateExpression instance.
     */
    public function max(string|ValueExpressionInterface $field): AggregateExpression
    {
        return $this->aggregate('MAX', $field);
    }

    /**
     * Builds a MIN function.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @return AggregateExpression The new AggregateExpression instance.
     */
    public function min(string|ValueExpressionInterface $field): AggregateExpression
    {
        return $this->aggregate('MIN', $field);
    }

    /**
     * Builds a current date or time expression.
     *
     * @param string $type The value type.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function now(string $type = 'datetime'): FunctionExpression
    {
        $type = strtolower($type);

        if (!in_array($type, ['date', 'datetime', 'time'], true)) {
            throw new InvalidArgumentException(sprintf(
                'Query function NOW type `%s` is not valid.',
                $type
            ));
        }

        return new FunctionExpression('NOW', [
            $type,
        ]);
    }

    /**
     * Builds an NTH_VALUE function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @param int $offset The row offset.
     * @return WindowExpression The new WindowExpression instance.
     */
    public function nthValue(string|ValueExpressionInterface $expression, int $offset): WindowExpression
    {
        if ($offset <= 0) {
            throw new InvalidArgumentException('Query function NTH_VALUE offset must be greater than zero.');
        }

        return $this->windowFunction('NTH_VALUE', [
            $this->valueExpression($expression),
            $offset,
        ]);
    }

    /**
     * Builds an NTILE function.
     *
     * @param int $buckets The number of buckets.
     * @return WindowExpression The new WindowExpression instance.
     */
    public function ntile(int $buckets): WindowExpression
    {
        if ($buckets <= 0) {
            throw new InvalidArgumentException('Query function NTILE buckets must be greater than zero.');
        }

        return $this->windowFunction('NTILE', [
            $buckets,
        ]);
    }

    /**
     * Builds a NULLIF function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @param mixed $value The comparison value.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function nullIf(string|ValueExpressionInterface $expression, mixed $value): FunctionExpression
    {
        return new FunctionExpression('NULLIF', [
            $this->valueExpression($expression),
            $value,
        ]);
    }

    /**
     * Builds a PERCENT_RANK function.
     *
     * @return WindowExpression The new WindowExpression instance.
     */
    public function percentRank(): WindowExpression
    {
        return $this->windowFunction('PERCENT_RANK');
    }

    /**
     * Builds a RANK function.
     *
     * @return WindowExpression The new WindowExpression instance.
     */
    public function rank(): WindowExpression
    {
        return $this->windowFunction('RANK');
    }

    /**
     * Builds a REPLACE function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @param mixed $search The search value.
     * @param mixed $replace The replacement value.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function replace(
        string|ValueExpressionInterface $expression,
        mixed $search,
        mixed $replace
    ): FunctionExpression {
        return new FunctionExpression('REPLACE', [
            $this->valueExpression($expression),
            $search,
            $replace,
        ]);
    }

    /**
     * Builds a ROUND function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @param int $precision The decimal precision.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function round(string|ValueExpressionInterface $expression, int $precision = 0): FunctionExpression
    {
        return new FunctionExpression('ROUND', [
            $this->valueExpression($expression),
            $precision,
        ]);
    }

    /**
     * Builds a ROW_NUMBER function.
     *
     * @return WindowExpression The new WindowExpression instance.
     */
    public function rowNumber(): WindowExpression
    {
        return $this->windowFunction('ROW_NUMBER');
    }

    /**
     * Builds a SUBSTRING function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @param int $start The start position.
     * @param int|null $length The substring length.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function substring(
        string|ValueExpressionInterface $expression,
        int $start,
        int|null $length = null
    ): FunctionExpression {
        if ($start <= 0) {
            throw new InvalidArgumentException('Query function SUBSTRING start must be greater than zero.');
        }

        if ($length !== null && $length < 0) {
            throw new InvalidArgumentException('Query function SUBSTRING length must not be negative.');
        }

        $arguments = [
            $this->valueExpression($expression),
            $start,
        ];

        if ($length !== null) {
            $arguments[] = $length;
        }

        return new FunctionExpression('SUBSTRING', $arguments);
    }

    /**
     * Builds a SUM function.
     *
     * @param string|ValueExpressionInterface $field The field.
     * @return AggregateExpression The new AggregateExpression instance.
     */
    public function sum(string|ValueExpressionInterface $field): AggregateExpression
    {
        return $this->aggregate('SUM', $field);
    }

    /**
     * Builds a TRIM function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function trim(string|ValueExpressionInterface $expression): FunctionExpression
    {
        return $this->valueFunction('TRIM', $expression);
    }

    /**
     * Builds an UPPER function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function upper(string|ValueExpressionInterface $expression): FunctionExpression
    {
        return $this->valueFunction('UPPER', $expression);
    }

    /**
     * Builds a weekday expression.
     *
     * @param string|ValueExpressionInterface $expression The date expression.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    public function weekDay(string|ValueExpressionInterface $expression): FunctionExpression
    {
        return new FunctionExpression('WEEK_DAY', [
            $this->valueExpression($expression),
        ]);
    }

    /**
     * Builds an aggregate function.
     *
     * @param string $name The function name.
     * @param string|ValueExpressionInterface $field The field.
     * @return AggregateExpression The new AggregateExpression instance.
     */
    protected function aggregate(string $name, string|ValueExpressionInterface $field): AggregateExpression
    {
        return new AggregateExpression($name, $this->valueExpression($field));
    }

    /**
     * Converts a string to an IdentifierExpression.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @return ValueExpressionInterface The query expression.
     */
    protected function valueExpression(string|ValueExpressionInterface $expression): ValueExpressionInterface
    {
        return is_string($expression) ?
            new IdentifierExpression($expression) :
            $expression;
    }

    /**
     * Builds a function with a single value expression.
     *
     * @param string $name The function name.
     * @param string|ValueExpressionInterface $expression The expression.
     * @return FunctionExpression The new FunctionExpression instance.
     */
    protected function valueFunction(string $name, string|ValueExpressionInterface $expression): FunctionExpression
    {
        return new FunctionExpression($name, [
            $this->valueExpression($expression),
        ]);
    }

    /**
     * Builds a window function.
     *
     * @param string $name The function name.
     * @param mixed[] $arguments The function arguments.
     * @return WindowExpression The new WindowExpression instance.
     */
    protected function windowFunction(string $name, array $arguments = []): WindowExpression
    {
        return new WindowExpression(
            new FunctionExpression($name, $arguments)
        );
    }

    /**
     * Builds the arguments for a window offset function.
     *
     * @param string|ValueExpressionInterface $expression The expression.
     * @param int $offset The row offset.
     * @param mixed $default The default value.
     * @return mixed[] The function arguments.
     */
    protected function windowOffsetArguments(
        string|ValueExpressionInterface $expression,
        int $offset,
        mixed $default
    ): array {
        $arguments = [
            $this->valueExpression($expression),
        ];

        if ($offset !== 1 || $default !== null) {
            $arguments[] = $offset;
        }

        if ($default !== null) {
            $arguments[] = $default;
        }

        return $arguments;
    }
}
