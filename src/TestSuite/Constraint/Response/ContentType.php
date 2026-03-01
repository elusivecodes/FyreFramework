<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Override;
use PHPUnit\Framework\Constraint\Constraint;

use function sprintf;
use function str_contains;
use function strtok;

/**
 * PHPUnit constraint asserting the response content type.
 *
 * Note: When the expected value does not include parameters, the actual header value is
 * compared without any parameters (e.g. the `; charset=...` portion).
 */
class ContentType extends Constraint
{
    /**
     * Constructs a ContentType.
     *
     * @param string $value The expected value.
     */
    public function __construct(
        protected string $value
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf(
            'is equal to "%s"',
            $this->value
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function failureDescription(mixed $other): string
    {
        return sprintf(
            'response content type %s',
            $this->toString()
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function matches(mixed $other): bool
    {
        $contentType = $other->getHeaderLine('Content-Type');

        if ($contentType && !str_contains($this->value, ';')) {
            $contentType = strtok($contentType, ';');
        }

        return $contentType === $this->value;
    }
}
