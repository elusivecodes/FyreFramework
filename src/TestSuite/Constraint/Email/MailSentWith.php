<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Constraint\Email;

use Fyre\Mail\Email;
use Override;
use PHPUnit\Framework\Constraint\Constraint;

use function array_any;
use function sprintf;

/**
 * PHPUnit constraint asserting a mail matches expected properties.
 *
 * Note: When `$at` is provided, it is treated as the (1-based) index into the mail list.
 */
class MailSentWith extends Constraint
{
    /**
     * Constructs a MailSentWith.
     *
     * @param string $expectedValue The expected value.
     * @param string $property The property to check.
     * @param int|null $at The index of the email.
     */
    public function __construct(
        protected string $expectedValue,
        protected string $property,
        protected int|null $at = null
    ) {}

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function toString(): string
    {
        return sprintf(
            'sent with %s address "%s"',
            $this->property,
            $this->expectedValue
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
        $method = match ($this->property) {
            'bcc' => 'getBcc',
            'cc' => 'getCc',
            'from' => 'getFrom',
            'sender' => 'getSender',
            'reply-to' => 'getReplyTo',
            'to' => 'getTo',
            default => null
        };

        if (!$method) {
            return false;
        }

        return array_any(
            $other,
            fn(Email $email): bool => isset($email->$method()[$this->expectedValue])
        );
    }
}
