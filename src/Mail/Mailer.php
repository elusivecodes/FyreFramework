<?php
declare(strict_types=1);

namespace Fyre\Mail;

use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Mail\Exceptions\MailException;

use function array_replace;
use function php_uname;

/**
 * Provides a base mailer implementation.
 */
abstract class Mailer
{
    use DebugTrait;
    use MacroTrait;

    /**
     * @var array<string, mixed>
     */
    protected static array $defaults = [
        'charset' => 'utf-8',
        'client' => null,
    ];

    /**
     * @var array<string, mixed>
     */
    protected array $config;

    /**
     * Constructs a Mailer.
     *
     * @param Container $container The Container.
     * @param array<string, mixed> $options The Mailer options.
     */
    public function __construct(
        protected Container $container,
        array $options = []
    ) {
        $this->config = array_replace(self::$defaults, static::$defaults, $options);
    }

    /**
     * Creates a new Email.
     *
     * @return Email The new Email instance.
     */
    public function email(): Email
    {
        return $this->container->build(Email::class, ['mailer' => $this]);
    }

    /**
     * Returns the client hostname.
     *
     * @return string The client hostname.
     */
    public function getClient(): string
    {
        if ($this->config['client']) {
            return $this->config['client'];
        }

        if (isset($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }

        if (isset($_SERVER['SERVER_ADDR'])) {
            return '['.$_SERVER['SERVER_ADDR'].']';
        }

        return php_uname('n');
    }

    /**
     * Returns the config.
     *
     * @return array<string, mixed> The config.
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Sends an email.
     *
     * @param Email $email The email to send.
     *
     * @throws MailException If the email has no recipients.
     */
    abstract public function send(Email $email): void;

    /**
     * Checks whether an email has recipients.
     *
     * @param Email $email The email to check.
     *
     * @throws MailException If the email has no recipients.
     */
    protected static function checkEmail(Email $email): void
    {
        if ($email->getRecipients() === []) {
            throw new MailException('Email sending must have a valid recipient.');
        }
    }
}
