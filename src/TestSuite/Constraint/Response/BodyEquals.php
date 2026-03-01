<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Override;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Util\Exporter;

use function sprintf;
use function strtr;

/**
 * PHPUnit constraint asserting the response body equals a value.
 *
 * Normalizes line endings before comparison.
 */
class BodyEquals extends Constraint
{
    /**
     * Constructs a BodyEquals.
     *
     * @param string $string The expected string.
     */
    public function __construct(
        protected string $string
    ) {
        $this->string = self::normalizeLineEndings($string);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf(
            'is equal to %s',
            Exporter::export($this->string)
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

        return self::normalizeLineEndings($contents) === $this->string;
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
