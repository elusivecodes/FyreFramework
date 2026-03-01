<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Email;

use Fyre\Mail\Email;
use Override;
use PHPUnit\Framework\Constraint\Constraint;

use function array_any;
use function sprintf;

/**
 * PHPUnit constraint asserting a mail contains an attachment.
 *
 * Note: When `$at` is provided, it is treated as the (1-based) index into the mail list.
 */
class MailContainsAttachment extends Constraint
{
    /**
     * Constructs a MailContainsAttachment.
     *
     * @param string $filename The expected filename.
     * @param int|null $at The index of the email.
     */
    public function __construct(
        protected string $filename,
        protected int|null $at = null
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf(
            'sent with attachment "%s"',
            $this->filename
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
                'email #%d was %s',
                $this->at,
                $this->toString()
            );
        }

        return sprintf(
            'an email was %s',
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
            fn(Email $email): bool => isset($email->getAttachments()[$this->filename])
        );
    }
}
