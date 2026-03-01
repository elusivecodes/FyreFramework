<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Email;

use Fyre\Mail\Email;
use Override;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Util\Exporter;

use function array_any;
use function sprintf;
use function str_contains;

/**
 * PHPUnit constraint asserting the mail subject contains a value.
 *
 * Note: When `$at` is provided, it is treated as the (1-based) index into the mail list.
 */
class MailSubjectContains extends Constraint
{
    /**
     * Constructs a MailSubjectContains.
     *
     * @param string $needle The expected string.
     * @param int|null $at The index of the email.
     */
    public function __construct(
        protected string $needle,
        protected int|null $at = null
    ) {}

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
    protected function failureDescription(mixed $other): string
    {
        if ($this->at !== null) {
            return sprintf(
                'email #%d subject %s',
                $this->at,
                $this->toString()
            );
        }

        return sprintf(
            'an email subject %s',
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
            fn(Email $email): bool => str_contains($email->getSubject(), $this->needle)
        );
    }
}
