<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Log;

use Override;
use PHPUnit\Util\Exporter;

use function array_any;
use function sprintf;
use function str_contains;

/**
 * PHPUnit constraint asserting a log message contains a value.
 */
class LogMessageContains extends LogMessage
{
    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf(
            'contains %s',
            Exporter::export($this->needle)
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

                return str_contains($message, $this->needle);
            }
        );
    }
}
