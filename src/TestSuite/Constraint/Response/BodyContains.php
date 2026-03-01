<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Override;
use PHPUnit\Framework\Constraint\Constraint;

use function mb_stripos;
use function mb_strtolower;
use function sprintf;
use function str_contains;
use function strtr;

/**
 * PHPUnit constraint asserting the response body contains a value.
 *
 * Normalizes line endings before comparison.
 */
class BodyContains extends Constraint
{
    /**
     * Constructs a BodyContains.
     *
     * @param string $needle The expected string.
     * @param bool $ignoreCase Whether to ignore case.
     */
    public function __construct(
        protected string $needle,
        protected bool $ignoreCase = false
    ) {
        $this->needle = self::normalizeLineEndings($needle);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        $needle = $this->needle;

        if ($this->ignoreCase) {
            $needle = mb_strtolower($this->needle, 'UTF-8');
        }

        return sprintf(
            'contains "%s"',
            $needle
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function failureDescription(mixed $other): string
    {
        return sprintf(
            'response body %s',
            $this->toString()
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function matches(mixed $other): bool
    {
        $contents = $other->getBody()->getContents();
        $haystack = self::normalizeLineEndings($contents);

        if ($this->ignoreCase) {
            return mb_stripos($haystack, $this->needle, 0, 'UTF-8') !== false;
        }

        return str_contains($haystack, $this->needle);
    }

    /**
     * Normalize line endings.
     *
     * @param string $string The string to normalize.
     * @return string The normalized string.
     */
    protected static function normalizeLineEndings(string $string): string
    {
        return strtr(
            $string,
            [
                "\r\n" => "\n",
                "\r" => "\n",
            ],
        );
    }
}
