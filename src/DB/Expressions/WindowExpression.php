<?php
declare(strict_types=1);

namespace Fyre\DB\Expressions;

use Fyre\Core\Traits\DebugTrait;
use InvalidArgumentException;

use function array_map;
use function array_merge;
use function is_array;
use function is_string;

/**
 * Represents a window function in a query.
 */
class WindowExpression implements ValueExpressionInterface
{
    use DebugTrait;

    protected string|null $exclude = null;

    /**
     * @var array{type: string, start: string, end: string}|null
     */
    protected array|null $frame = null;

    /**
     * @var array<string>
     */
    protected array $orderBy = [];

    /**
     * @var ValueExpressionInterface[]
     */
    protected array $partitionBy = [];

    /**
     * Constructs a WindowExpression.
     *
     * @param FunctionExpression $function The function expression.
     */
    public function __construct(protected FunctionExpression $function) {}

    /**
     * Excludes the current row from the window frame.
     *
     * @return static The WindowExpression instance.
     */
    public function excludeCurrent(): static
    {
        $this->exclude = 'CURRENT ROW';

        return $this;
    }

    /**
     * Excludes the current group from the window frame.
     *
     * @return static The WindowExpression instance.
     */
    public function excludeGroup(): static
    {
        $this->exclude = 'GROUP';

        return $this;
    }

    /**
     * Excludes ties from the window frame.
     *
     * @return static The WindowExpression instance.
     */
    public function excludeTies(): static
    {
        $this->exclude = 'TIES';

        return $this;
    }

    /**
     * Returns the window frame exclusion.
     *
     * @return string|null The window frame exclusion.
     */
    public function getExclude(): string|null
    {
        return $this->exclude;
    }

    /**
     * Returns the window frame.
     *
     * @return array{type: string, start: string, end: string}|null The window frame.
     */
    public function getFrame(): array|null
    {
        return $this->frame;
    }

    /**
     * Returns the function expression.
     *
     * @return FunctionExpression The function expression.
     */
    public function getFunction(): FunctionExpression
    {
        return $this->function;
    }

    /**
     * Returns the ORDER BY fields.
     *
     * @return array<string> The ORDER BY fields.
     */
    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    /**
     * Returns the partition fields.
     *
     * @return ValueExpressionInterface[] The partition fields.
     */
    public function getPartitionBy(): array
    {
        return $this->partitionBy;
    }

    /**
     * Sets a GROUPS frame.
     *
     * @param int|null $start The starting offset.
     * @param int|null $end The ending offset.
     * @return static The WindowExpression instance.
     */
    public function groups(int|null $start, int|null $end = 0): static
    {
        return $this->frame('GROUPS', $start, $end);
    }

    /**
     * Sets the ORDER BY fields.
     *
     * @param array<string>|string $fields The fields.
     * @param bool $overwrite Whether to overwrite the existing fields.
     * @return static The WindowExpression instance.
     */
    public function orderBy(array|string $fields, bool $overwrite = false): static
    {
        $fields = (array) $fields;

        if ($overwrite) {
            $this->orderBy = $fields;
        } else {
            $this->orderBy = array_merge($this->orderBy, $fields);
        }

        return $this;
    }

    /**
     * Sets the partition fields.
     *
     * @param array<string|ValueExpressionInterface>|string|ValueExpressionInterface $fields The fields.
     * @param bool $overwrite Whether to overwrite the existing fields.
     * @return static The WindowExpression instance.
     */
    public function partitionBy(
        array|string|ValueExpressionInterface $fields,
        bool $overwrite = false
    ): static {
        $fields = is_array($fields) ?
            $fields :
            [$fields];

        $fields = array_map(
            static fn(string|ValueExpressionInterface $field): ValueExpressionInterface => is_string($field) ?
                new IdentifierExpression($field) :
                $field,
            $fields
        );

        if ($overwrite) {
            $this->partitionBy = $fields;
        } else {
            $this->partitionBy = array_merge($this->partitionBy, $fields);
        }

        return $this;
    }

    /**
     * Sets a RANGE frame.
     *
     * @param int|null $start The starting offset.
     * @param int|null $end The ending offset.
     * @return static The WindowExpression instance.
     */
    public function range(int|null $start, int|null $end = 0): static
    {
        return $this->frame('RANGE', $start, $end);
    }

    /**
     * Sets a ROWS frame.
     *
     * @param int|null $start The starting offset.
     * @param int|null $end The ending offset.
     * @return static The WindowExpression instance.
     */
    public function rows(int|null $start, int|null $end = 0): static
    {
        return $this->frame('ROWS', $start, $end);
    }

    /**
     * Sets the window frame.
     *
     * @param string $type The frame type.
     * @param int|null $start The starting offset.
     * @param int|null $end The ending offset.
     * @return static The WindowExpression instance.
     *
     * @throws InvalidArgumentException If a frame offset is not valid.
     */
    protected function frame(string $type, int|null $start, int|null $end): static
    {
        $this->frame = [
            'type' => $type,
            'start' => static::frameBoundary($start, 'PRECEDING'),
            'end' => static::frameBoundary($end, 'FOLLOWING'),
        ];

        return $this;
    }

    /**
     * Builds a window frame boundary.
     *
     * @param int|null $offset The frame offset.
     * @param string $direction The frame direction.
     * @return string The window frame boundary.
     *
     * @throws InvalidArgumentException If the frame offset is not valid.
     */
    protected static function frameBoundary(int|null $offset, string $direction): string
    {
        if ($offset === null) {
            return 'UNBOUNDED '.$direction;
        }

        if ($offset < 0) {
            throw new InvalidArgumentException('Query window frame offset must not be negative.');
        }

        return $offset === 0 ?
            'CURRENT ROW' :
            $offset.' '.$direction;
    }
}
