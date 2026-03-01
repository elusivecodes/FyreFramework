<?php
declare(strict_types=1);

namespace Fyre\Utility;

use Fyre\Core\Config;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use InvalidArgumentException;

use function array_search;
use function count;
use function htmlspecialchars;
use function is_array;
use function is_bool;
use function is_numeric;
use function is_object;
use function json_encode;
use function preg_match;
use function preg_replace;
use function strtolower;
use function substr;
use function uksort;

use const ENT_HTML5;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

/**
 * Provides HTML helper utilities.
 *
 * This class focuses on safe escaping and deterministic attribute rendering for HTML tag generation.
 */
class HtmlHelper
{
    use DebugTrait;
    use MacroTrait;

    protected const ATTRIBUTES_ORDER = [
        'class',
        'id',
        'name',
        'data-',
        'src',
        'for',
        'type',
        'href',
        'action',
        'method',
        'value',
        'title',
        'alt',
        'role',
        'aria-',
    ];

    protected string $charset = 'UTF-8';

    /**
     * Constructs an HtmlHelper.
     *
     * @param Config $config The Config.
     */
    public function __construct(Config $config)
    {
        $this->charset = $config->get('App.charset', 'UTF-8');
    }

    /**
     * Generates an attribute string.
     *
     * Produces a string starting with a space, suitable for concatenation
     * directly after a tag name, e.g. `<input` . $html->attributes([...]) . '>' .
     * Values are HTML-escaped; arrays/objects are JSON-encoded.
     *
     * Note: Boolean `true` renders a valueless attribute (e.g. `disabled`) and boolean `false` renders `="false"`.
     * Numeric keys are treated as valueless attributes.
     *
     * @param array<mixed> $options The attributes.
     * @return string The attribute string.
     *
     * @throws InvalidArgumentException If an array or object value cannot be JSON-encoded.
     */
    public function attributes(array $options = []): string
    {
        $attributes = [];

        foreach ($options as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_numeric($key)) {
                $key = $value;
                $value = null;
            }

            $key = ((string) preg_replace('/[^\w\-:.@]/', '', $key)) |> strtolower(...);

            if (!$key) {
                continue;
            }

            if (is_bool($value)) {
                $value = $value ? null : 'false';
            } else if (is_array($value) || is_object($value)) {
                $json = json_encode($value);
                if ($json === false) {
                    throw new InvalidArgumentException('Unable to JSON-encode attribute value.');
                }
                $value = $this->escape($json);

            } else if ($value !== null) {
                $value = $this->escape((string) $value);
            }

            $attributes[$key] = $value;
        }

        if ($attributes === []) {
            return '';
        }

        uksort(
            $attributes,
            static fn(string $a, string $b): int => static::attributeIndex($a) <=> static::attributeIndex($b)
        );

        $html = '';

        foreach ($attributes as $key => $value) {
            if ($value !== null) {
                $html .= ' '.$key.'="'.$value.'"';
            } else {
                $html .= ' '.$key;
            }
        }

        return $html;
    }

    /**
     * Escapes characters in a string for use in HTML.
     *
     * @param string $string The input string.
     * @return string The escaped string.
     */
    public function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, $this->charset);
    }

    /**
     * Returns the charset.
     *
     * @return string The charset.
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * Sets the charset.
     *
     * @param string $charset The charset.
     * @return static The HtmlHelper instance.
     */
    public function setCharset(string $charset): static
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Returns the index for an attribute.
     *
     * @param string $attribute The attribute name.
     * @return int The attribute index.
     */
    protected static function attributeIndex(string $attribute): int
    {
        if (preg_match('/^(data|aria)-/', $attribute)) {
            $attribute = substr($attribute, 0, 5);
        }

        $index = array_search($attribute, static::ATTRIBUTES_ORDER, true);

        if ($index === false) {
            return count(static::ATTRIBUTES_ORDER);
        }

        return (int) $index;
    }
}
