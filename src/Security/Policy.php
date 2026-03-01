<?php
declare(strict_types=1);

namespace Fyre\Security;

use Fyre\Core\Traits\DebugTrait;
use InvalidArgumentException;

use function array_map;
use function implode;
use function in_array;
use function is_string;
use function preg_match;
use function sprintf;

/**
 * Represents a Content Security Policy (CSP) directive set.
 *
 * This class is immutable; modifier methods return a cloned instance.
 */
class Policy
{
    use DebugTrait;

    protected const VALID_DIRECTIVES = [
        'base-uri',
        'block-all-mixed-content',
        'child-src',
        'connect-src',
        'default-src',
        'font-src',
        'form-action',
        'frame-src',
        'frame-ancestors',
        'img-src',
        'manifest-src',
        'media-src',
        'object-src',
        'plugin-types',
        'prefetch-src',
        'report-uri',
        'report-to',
        'sandbox',
        'script-src',
        'script-src-attr',
        'script-src-elem',
        'style-src',
        'style-src-attr',
        'style-src-elem',
        'upgrade-insecure-requests',
        'webrtc',
        'worker-src',
    ];

    protected const VALID_SOURCES = [
        'none',
        'report-sample',
        'self',
        'strict-dynamic',
        'unsafe-eval',
        'unsafe-hashes',
        'unsafe-inline',
    ];

    /**
     * @var array<string, string[]>
     */
    protected array $directives = [];

    /**
     * Constructs a Policy.
     *
     * @param array<string, bool|string|string[]> $directives The policy directives.
     */
    public function __construct(array $directives = [])
    {
        foreach ($directives as $directive => $values) {
            static::checkDirective($directive);

            if ($values === false) {
                continue;
            }

            $this->directives[$directive] = [];

            if ($values === true) {
                $values = [];
            } else if (is_string($values)) {
                $values = [$values];
            }

            foreach ($values as $v) {
                $this->directives[$directive][] = $v;
            }
        }
    }

    /**
     * Returns the header string.
     *
     * @return string The header string.
     */
    public function __toString(): string
    {
        return $this->getHeaderString();
    }

    /**
     * Returns the options for a directive.
     *
     * @param string $directive The directive.
     * @return string[]|null The directive options.
     */
    public function getDirective(string $directive): array|null
    {
        static::checkDirective($directive);

        return $this->directives[$directive] ?? null;
    }

    /**
     * Returns the header string.
     *
     * Note: Known source keywords are quoted (e.g. `'self'`). Nonces and hashes are also
     * quoted when provided as `nonce-...` or `sha(256|384|512)-...`.
     *
     * @return string The header string.
     */
    public function getHeaderString(): string
    {
        $directives = [];

        foreach ($this->directives as $directive => $values) {
            $valueString = $directive;

            if ($values !== []) {
                $valueString .= ' ';
                $valueString .= static::formatSrc($values);
            }

            $directives[] = $valueString.';';
        }

        return implode(' ', $directives);
    }

    /**
     * Checks whether a directive exists.
     *
     * @param string $directive The directive.
     * @return bool Whether the directive exists.
     */
    public function hasDirective(string $directive): bool
    {
        static::checkDirective($directive);

        return isset($this->directives[$directive]);
    }

    /**
     * Clones the Policy with new directive options.
     *
     * @param string $directive The directive.
     * @param bool|string|string[] $value The value.
     * @return Policy The new Policy instance.
     */
    public function withDirective(string $directive, array|bool|string $value = true): Policy
    {
        if ($value === false) {
            return $this->withoutDirective($directive);
        }

        static::checkDirective($directive);

        $temp = clone $this;

        $temp->directives[$directive] ??= [];

        if ($value === true) {
            $value = [];
        } else if (is_string($value)) {
            $value = [$value];
        }

        foreach ($value as $v) {
            if (in_array($v, $temp->directives[$directive], true)) {
                continue;
            }

            $temp->directives[$directive][] = $v;
        }

        return $temp;
    }

    /**
     * Clones the Policy without a directive.
     *
     * @param string $directive The directive.
     * @return static The new Policy instance.
     */
    public function withoutDirective(string $directive): static
    {
        static::checkDirective($directive);

        $temp = clone $this;

        unset($temp->directives[$directive]);

        return $temp;
    }

    /**
     * Checks whether a directive is valid.
     *
     * @param string $directive The directive.
     *
     * @throws InvalidArgumentException If the directive is not valid.
     */
    protected static function checkDirective(string $directive): void
    {
        if (!in_array($directive, static::VALID_DIRECTIVES, true)) {
            throw new InvalidArgumentException(sprintf(
                'CSP directive `%s` is not valid.',
                $directive
            ));
        }
    }

    /**
     * Format source values from an array.
     *
     * @param string[] $sources The sources.
     * @return string The formatted string.
     */
    protected static function formatSrc(array $sources): string
    {
        $sources = array_map(
            static function(string $source): string {
                if (in_array($source, static::VALID_SOURCES, true) || preg_match('/^(nonce|sha(256|384|512)\-).+$/', $source)) {
                    return '\''.$source.'\'';
                }

                return $source;
            },
            $sources
        );

        return implode(' ', $sources);
    }
}
