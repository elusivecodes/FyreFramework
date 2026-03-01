<?php
declare(strict_types=1);

namespace Fyre\TestSuite\Mail\Handlers;

use Fyre\Mail\Email;
use Fyre\Mail\Mailer;
use Override;

/**
 * Mailer handler that captures messages for testing.
 *
 * Messages are stored in-memory and can be retrieved via {@see self::getMessages()}.
 */
class TestMailer extends Mailer
{
    /**
     * @var Email[]
     */
    protected static array $messages = [];

    /**
     * Clear sent messages.
     */
    public static function clearMessages(): void
    {
        static::$messages = [];
    }

    /**
     * Returns the sent messages.
     *
     * @return Email[] The sent messages.
     */
    public static function getMessages(): array
    {
        return static::$messages;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function send(Email $email): void
    {
        static::$messages[] = $email;
    }
}
