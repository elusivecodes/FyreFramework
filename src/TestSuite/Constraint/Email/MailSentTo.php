<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Email;

use Override;

use function sprintf;

/**
 * PHPUnit constraint asserting a mail recipient address.
 *
 * Note: When `$at` is provided, it is treated as the (1-based) index into the mail list.
 */
class MailSentTo extends MailSentWith
{
    /**
     * Constructs a MailSentTo.
     *
     * @param string $expectedValue The expected value.
     * @param int|null $at The index of the email.
     */
    public function __construct(
        protected string $expectedValue,
        int|null $at = null
    ) {
        parent::__construct($expectedValue, 'to', $at);
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf(
            'sent to "%s"',
            $this->expectedValue
        );
    }
}
