<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Log;

use Override;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Util\Exporter;

use function array_any;
use function sprintf;
use function str_starts_with;
use function strlen;
use function strtoupper;
use function substr;

/**
 * PHPUnit constraint asserting a log message entry.
 */
class LogMessage extends Constraint
{
    /**
     * Constructs a LogMessage.
     *
     * @param string $needle The expected string.
     * @param string $level The log level.
     */
    public function __construct(
        protected string $needle,
        protected string $level
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf(
            'has the message %s',
            Exporter::export($this->needle)
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function failureDescription(mixed $other): string
    {
        return sprintf(
            'the "%s" log %s',
            $this->level,
            $this->toString()
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function matches(mixed $other): bool
    {
        return array_any(
            $other,
            function(string $message): bool {
                $message = $this->removeLevel($message);

                if ($message === null) {
                    return false;
                }

                return $message === $this->needle;
            }
        );
    }

    /**
     * Remove the level from a log entry.
     *
     * @param string $message The log message.
     * @return string|null The message with the level removed.
     */
    protected function removeLevel(string $message): string|null
    {
        $prefix = '['.strtoupper($this->level).'] ';

        if (!str_starts_with($message, $prefix)) {
            return null;
        }

        return substr($message, strlen($prefix));
    }
}
