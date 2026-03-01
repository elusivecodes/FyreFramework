<?php
declare(strict_types=1);

namespace Fyre\Mail\Handlers;

use Fyre\Mail\Email;
use Fyre\Mail\Mailer;
use Override;

/**
 * Captures messages for debugging.
 */
class DebugMailer extends Mailer
{
    /**
     * @var array<string, mixed>[]
     */
    protected array $sentEmails = [];

    /**
     * Clears the sent emails.
     */
    public function clear(): void
    {
        $this->sentEmails = [];
    }

    /**
     * Returns the sent emails.
     *
     * @return array<string, mixed>[] The sent emails.
     */
    public function getSentEmails(): array
    {
        return $this->sentEmails;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function send(Email $email): void
    {
        static::checkEmail($email);

        $this->sentEmails[] = [
            'headers' => $email->getFullHeaders(),
            'body' => $email->getFullBodyString(),
        ];
    }
}
