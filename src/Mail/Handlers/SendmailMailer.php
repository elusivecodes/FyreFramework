<?php
declare(strict_types=1);

namespace Fyre\Mail\Handlers;

use Fyre\Core\Exceptions\ErrorException;
use Fyre\Mail\Email;
use Fyre\Mail\Mailer;
use Override;
use RuntimeException;

use function assert;
use function is_string;
use function mail;

/**
 * Sends mail via PHP's {@see mail()} function.
 */
class SendmailMailer extends Mailer
{
    /**
     * {@inheritDoc}
     *
     * @throws ErrorException|RuntimeException If the email could not be sent.
     */
    #[Override]
    public function send(Email $email): void
    {
        static::checkEmail($email);

        $headers = $email->getFullHeaders();
        $body = $email->getFullBodyString();

        $to = $headers['To'] ?? '';
        $subject = $headers['Subject'] ?? '';

        assert(is_string($to));
        assert(is_string($subject));

        unset($headers['To']);
        unset($headers['Subject']);

        if (!mail($to, $subject, $body, $headers)) {
            throw ErrorException::forLastError(__FILE__, __LINE__ - 1) ?? new RuntimeException();
        }
    }
}
