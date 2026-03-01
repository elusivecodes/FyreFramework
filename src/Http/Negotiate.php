<?php
declare(strict_types=1);

namespace Fyre\Http;

use Fyre\Core\Traits\StaticMacroTrait;
use InvalidArgumentException;

use function array_merge;
use function array_reduce;
use function array_shift;
use function array_unique;
use function count;
use function explode;
use function in_array;
use function preg_match;
use function strtok;
use function substr_count;
use function trim;
use function usort;

/**
 * Provides best-match selection for common `Accept-*` headers.
 *
 * Supports content type, encoding, and language negotiation.
 */
abstract class Negotiate
{
    use StaticMacroTrait;

    /**
     * Returns the negotiated content type.
     *
     * @param string $accepted The accept content header.
     * @param string[] $supported The supported content types.
     * @param bool $strict Whether to avoid falling back to the first supported value when no match is found.
     * @return string The negotiated content type, or an empty string if strict and no match is found.
     */
    public static function content(string $accepted, array $supported, bool $strict = false): string
    {
        return static::getBestMatch($accepted, $supported, true, strict: $strict);
    }

    /**
     * Returns the negotiated encoding.
     *
     * @param string $accepted The accept encoding header.
     * @param string[] $supported The supported encodings.
     * @return string The negotiated encoding.
     */
    public static function encoding(string $accepted, array $supported): string
    {
        $supported = array_merge($supported, ['identity']) |> array_unique(...);

        return static::getBestMatch($accepted, $supported);
    }

    /**
     * Returns the negotiated language.
     *
     * @param string $accepted The accept language header.
     * @param string[] $supported The supported languages.
     * @return string The negotiated language.
     */
    public static function language(string $accepted, array $supported): string
    {
        return static::getBestMatch($accepted, $supported, matchLocales: true);
    }

    /**
     * Returns the best match for a header.
     *
     * @param string $accepted The accepted header value.
     * @param string[] $supported The supported values (first value is the default fallback when not strict).
     * @param bool $enforceTypes Whether to check sub types (e.g. "text/*").
     * @param bool $matchLocales Whether to match only the primary locale (e.g. "en" vs "en-US").
     * @param bool $strict Whether to avoid falling back to the first supported value when no match is found.
     * @return string The best match, or an empty string if strict and no match is found.
     *
     * @throws InvalidArgumentException If no supported values are supplied.
     */
    protected static function getBestMatch(string $accepted, array $supported, bool $enforceTypes = false, bool $matchLocales = false, bool $strict = false): string
    {
        if ($supported === []) {
            throw new InvalidArgumentException('No supported values supplied.');
        }

        if ($strict) {
            $default = '';
        } else {
            $default = $supported[0] ?? '';
        }

        if (!$accepted) {
            return $default;
        }

        $accepted = static::parseHeader($accepted);

        $supported = array_reduce(
            array_unique($supported),
            static fn(array $acc, string $value): array => array_merge($acc, static::parseHeader($value)),
            []
        );

        foreach ($accepted as $a) {
            if (!$a['q']) {
                continue;
            }

            if ($a['value'] === '*' || $a['value'] === '*/*') {
                return $supported[0]['value'];
            }

            foreach ($supported as $b) {
                if (static::match($a, $b, $enforceTypes, $matchLocales)) {
                    return $b['value'];
                }
            }
        }

        return $default;
    }

    /**
     * Matches values.
     *
     * @param array<string, mixed> $a The first value.
     * @param array<string, mixed> $b The second value.
     * @param bool $enforceTypes Whether to check sub types.
     * @param bool $matchLocales Whether to match locales.
     * @return bool Whether the values match.
     */
    protected static function match(array $a, array $b, bool $enforceTypes = false, bool $matchLocales = false): bool
    {
        if ($a['value'] === $b['value']) {
            return static::matchParameters($a['params'], $b['params']);
        }

        if ($enforceTypes) {
            return static::matchSubTypes($a['value'], $b['value']);
        }

        if ($matchLocales) {
            return static::matchLocales($a['value'], $b['value']);
        }

        return false;
    }

    /**
     * Matches locale strings.
     *
     * @param string $a The first locale string.
     * @param string $b The second locale string.
     * @return bool Whether the locale strings match.
     */
    protected static function matchLocales(string $a, string $b): bool
    {
        return strtok($a, '-') === strtok($b, '-');
    }

    /**
     * Matches parameters.
     *
     * @param array<string, string> $a The first parameters.
     * @param array<string, string> $b The second parameters.
     * @return bool Whether the parameters match.
     */
    protected static function matchParameters(array $a, array $b): bool
    {
        if (count($a) !== count($b)) {
            return false;
        }

        foreach ($b as $label => $value) {
            $test = $a[$label] ?? null;

            if ($test !== $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Matches sub types.
     *
     * @param string $a The first value.
     * @param string $b The second value.
     * @return bool Whether the sub types match.
     */
    protected static function matchSubTypes(string $a, string $b): bool
    {
        [$aType, $aSubType] = explode('/', $a, 2);
        [$bType, $bSubType] = explode('/', $b, 2);

        if ($aType !== $bType) {
            return false;
        }

        if (in_array('*', [$aSubType, $bSubType], true)) {
            return true;
        }

        return $aSubType === $bSubType;
    }

    /**
     * Parses a header for accepted values.
     *
     * @param string $header The header string.
     * @return array<string, mixed>[] The accepted values sorted by quality (q) and specificity.
     */
    protected static function parseHeader(string $header): array
    {
        $results = [];
        $parts = explode(',', $header);

        foreach ($parts as $part) {
            $pairs = explode(';', $part);
            $value = array_shift($pairs) |> trim(...);

            if ($value === '') {
                continue;
            }

            $parameters = [];

            foreach ($pairs as $pair) {
                if (!preg_match('/^(.+?)=(["\']?)(.*?)(?:\2)$/', $pair, $match)) {
                    continue;
                }

                $name = trim($match[1]);
                $val = trim($match[3]);

                $parameters[$name] = $val;
            }

            $quality = $parameters['q'] ?? 1;
            unset($parameters['q']);

            $results[] = [
                'value' => trim($value),
                'q' => (float) $quality,
                'params' => $parameters,
            ];
        }

        usort($results, static function(array $a, array $b): int {
            if ($a['q'] !== $b['q']) {
                return $b['q'] <=> $a['q'];
            }

            $aWild = substr_count($a['value'], '*');
            $bWild = substr_count($b['value'], '*');

            if ($aWild !== $bWild) {
                return $aWild <=> $bWild;
            }

            $aParams = count($a['params']);
            $bParams = count($b['params']);

            return $bParams <=> $aParams;
        });

        return $results;
    }
}
