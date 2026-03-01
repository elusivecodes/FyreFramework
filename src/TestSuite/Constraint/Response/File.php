<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Response;

use Fyre\Http\DownloadResponse;
use Override;
use PHPUnit\Framework\Constraint\Constraint;

use function file_get_contents;
use function sprintf;

/**
 * PHPUnit constraint asserting the response represents a file.
 */
class File extends Constraint
{
    /**
     * Constructs a File.
     *
     * @param string $value The expected file path.
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
            'download response file %s',
            $this->toString()
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function matches(mixed $other): bool
    {
        return $other instanceof DownloadResponse &&
            $other->getBody()->getContents() === file_get_contents($this->value);
    }
}
