<?php
declare(strict_types=1);

namespace Fyre\DB;

use Fyre\Core\Traits\DebugTrait;
use Override;
use Stringable;

/**
 * Raw SQL fragment wrapper.
 *
 * Use this to embed literal SQL expressions into generated queries without binding.
 */
class QueryLiteral implements Stringable
{
    use DebugTrait;

    /**
     * Constructs a QueryLiteral.
     *
     * @param string $string The literal string.
     */
    public function __construct(
        protected string $string
    ) {}

    /**
     * Returns the literal string.
     *
     * @return string The literal string.
     */
    #[Override]
    public function __toString(): string
    {
        return $this->string;
    }
}
