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
use function strtr;

/**
 * PHPUnit constraint asserting the mail body contains a value.
 *
 * Normalizes line endings before comparison.
 *
 * Note: When `$at` is provided, it is treated as the (1-based) index into the mail list.
 */
class MailBodyContains extends Constraint
{
    /**
     * Constructs a MailBodyContains.
     *
     * @param string $needle The expected string.
     * @param string|null $bodyType The type of body.
     * @param int|null $at The index of the email.
     */
    public function __construct(
        protected string $needle,
        protected string|null $bodyType = null,
        protected int|null $at = null
    ) {
        $this->needle = self::normalizeLineEndings($needle);
    }

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
        $type = match ($this->bodyType) {
            'html' => 'HTML body',
            'text' => 'text body',
            default => 'body',
        };

        if ($this->at !== null) {
            return sprintf(
                'email #%d %s %s',
                $this->at,
                $type,
                $this->toString()
            );
        }

        return sprintf(
            'an email %s %s',
            $type,
            $this->toString()
        );
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    protected function matches(mixed $other): bool
    {
        $method = match ($this->bodyType) {
            'html' => 'getBodyHtml',
            'text' => 'getBodyText',
            default => 'getFullBodyString',
        };

        return array_any(
            $other,
            fn(Email $email): bool => str_contains(
                self::normalizeLineEndings($email->$method()),
                $this->needle
            )
        );
    }

    /**
     * Normalize the line endings.
     *
     * @param string $string The string to normalize.
     * @return string The normalized string.
     */
    protected static function normalizeLineEndings(string $string): string
    {
        return strtr(
            $string,
            [
                "\r\n" => "\n",
                "\r" => "\n",
            ],
        );
    }
}
