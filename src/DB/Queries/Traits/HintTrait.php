<?php
declare(strict_types=1);

namespace Fyre\DB\Queries\Traits;

use BadMethodCallException;
use Fyre\DB\DbFeature;
use Fyre\DB\Query;
use InvalidArgumentException;

use function array_merge;
use function is_string;
use function str_contains;
use function trim;

/**
 * Adds optimizer hint support to queries.
 *
 * @phpstan-require-extends Query
 */
trait HintTrait
{
    /**
     * @var string[]
     */
    protected array $hints = [];

    /**
     * Returns the optimizer hints.
     *
     * @return string[] The optimizer hints.
     */
    public function getHints(): array
    {
        return $this->hints;
    }

    /**
     * Sets the optimizer hints.
     *
     * @param string|string[] $hints The optimizer hints.
     * @param bool $overwrite Whether to overwrite the existing optimizer hints.
     * @return static The Query instance.
     *
     * @throws BadMethodCallException If optimizer hints are not supported.
     * @throws InvalidArgumentException If an optimizer hint is not valid.
     */
    public function hint(array|string $hints, bool $overwrite = false): static
    {
        if (!$this->connection->supports(DbFeature::OptimizerHints)) {
            throw new BadMethodCallException('Optimizer hints are not supported by this connection.');
        }

        $hints = (array) $hints;

        foreach ($hints as $hint) {
            if (
                !is_string($hint) ||
                trim($hint) === '' ||
                str_contains($hint, '/*') ||
                str_contains($hint, '*/')
            ) {
                throw new InvalidArgumentException('Query optimizer hint is not valid.');
            }
        }

        if ($overwrite) {
            $this->hints = $hints;
        } else {
            $this->hints = array_merge($this->hints, $hints);
        }

        $this->dirty();

        return $this;
    }
}
