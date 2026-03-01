<?php
declare(strict_types=1);

namespace Fyre\Utility;

use Fyre\Core\Config;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\Utility\DateTime\DateTime;
use IntlDatePatternGenerator;
use IntlListFormatter;
use NumberFormatter;

use function locale_get_default;

/**
 * Provides formatting utilities.
 *
 * This class wraps PHP's Intl formatters and caches formatter instances per locale/pattern for performance.
 */
class Formatter
{
    use DebugTrait;
    use MacroTrait;

    /**
     * @var array<string, string>
     */
    protected array $dateFormats = [];

    /**
     * @var array<string, IntlDatePatternGenerator>
     */
    protected array $datePatternGenerators = [];

    protected string $defaultCurrency = 'USD';

    protected string|null $defaultLocale = null;

    /**
     * @var array<string, IntlListFormatter>
     */
    protected array $listFormatters = [];

    /**
     * @var array<string, NumberFormatter[]>
     */
    protected array $numberFormatters = [];

    /**
     * Constructs a Formatter.
     *
     * @param Config $config The Config.
     */
    public function __construct(Config $config)
    {
        $this->defaultCurrency = $config->get('App.defaultCurrency', 'USD');
        $this->defaultLocale = $config->get('App.defaultLocale');
    }

    /**
     * Formats a value as a currency string.
     *
     * @param float|int|string $value The value.
     * @param string|null $currency The currency.
     * @param string|null $locale The locale.
     * @return string The currency string.
     */
    public function currency(float|int|string $value, string|null $currency = null, string|null $locale = null): string
    {
        $currency ??= $this->getDefaultCurrency();
        $locale ??= $this->getDefaultLocale();

        return (string) $this->getNumberFormatter($locale, NumberFormatter::CURRENCY_ACCOUNTING)
            ->formatCurrency((float) $value, $currency);
    }

    /**
     * Formats a DateTime as a date string.
     *
     * @param DateTime $value The DateTime.
     * @param string|null $format The format.
     * @param string|null $timeZone The time zone.
     * @param string|null $locale The locale.
     * @return string The date string.
     */
    public function date(DateTime $value, string|null $format = null, string|null $timeZone = null, string|null $locale = null): string
    {
        $locale ??= $this->getDefaultLocale();
        $format ??= $this->getBestDateFormat($locale, 'yyyyMMdd');

        return $this->datetime($value, $format, $timeZone, $locale);
    }

    /**
     * Formats a DateTime as a date/time string.
     *
     * @param DateTime $value The DateTime.
     * @param string|null $format The format.
     * @param string|null $timeZone The time zone.
     * @param string|null $locale The locale.
     * @return string The date/time string.
     *
     * Note: If a locale or time zone is provided, the DateTime is cloned with the new settings before formatting.
     */
    public function datetime(DateTime $value, string|null $format = null, string|null $timeZone = null, string|null $locale = null): string
    {
        $locale ??= $this->getDefaultLocale();
        $format ??= $this->getBestDateFormat($locale, 'yyyyMMddjmm');

        if ($value->getLocale() !== $locale) {
            $value = $value->withLocale($locale);
        }

        if ($timeZone && $value->getTimeZone() !== $timeZone) {
            $value = $value->withTimeZone($timeZone);
        }

        return $value->format($format);
    }

    /**
     * Returns the default currency.
     *
     * @return string The default currency.
     */
    public function getDefaultCurrency(): string
    {
        return $this->defaultCurrency;
    }

    /**
     * Returns the default locale.
     *
     * @return string The default locale.
     */
    public function getDefaultLocale(): string
    {
        return $this->defaultLocale ?? locale_get_default();
    }

    /**
     * Formats an array as a list.
     *
     * @param array<string> $data The data.
     * @param string|null $conjunction The conjunction ("and", "or", or null).
     * @param string $width The width ("wide", "short", or "narrow").
     * @param string|null $locale The locale.
     * @return string The list.
     */
    public function list(array $data, string|null $conjunction = 'and', string $width = 'wide', string|null $locale = null): string
    {
        $locale ??= $this->getDefaultLocale();
        $type = match ($conjunction) {
            'and' => IntlListFormatter::TYPE_AND,
            'or' => IntlListFormatter::TYPE_OR,
            default => IntlListFormatter::TYPE_UNITS
        };
        $width = match ($width) {
            'short' => IntlListFormatter::WIDTH_SHORT,
            'narrow' => IntlListFormatter::WIDTH_NARROW,
            default => IntlListFormatter::WIDTH_WIDE
        };

        return (string) $this->getListFormatter($locale, $type, $width)->format($data);
    }

    /**
     * Formats a value as a number string.
     *
     * @param float|int|string $value The value.
     * @param string|null $locale The locale.
     * @return string The number string.
     */
    public function number(float|int|string $value, string|null $locale = null): string
    {
        $locale ??= $this->getDefaultLocale();

        return (string) $this->getNumberFormatter($locale)
            ->format((float) $value);
    }

    /**
     * Formats a value as a percent string.
     *
     * @param float|int|string $value The value.
     * @param string|null $locale The locale.
     * @return string The percent string.
     */
    public function percent(float|int|string $value, string|null $locale = null): string
    {
        $locale ??= $this->getDefaultLocale();

        return (string) $this->getNumberFormatter($locale, NumberFormatter::PERCENT)
            ->format((float) $value);
    }

    /**
     * Sets the default currency.
     *
     * @param string $currency The currency.
     * @return static The Formatter instance.
     */
    public function setDefaultCurrency(string $currency): static
    {
        $this->defaultCurrency = $currency;

        return $this;
    }

    /**
     * Sets the default locale.
     *
     * @param string|null $locale The locale.
     * @return static The Formatter instance.
     */
    public function setDefaultLocale(string|null $locale): static
    {
        $this->defaultLocale = $locale;

        return $this;
    }

    /**
     * Formats a DateTime as a time string.
     *
     * @param DateTime $value The DateTime.
     * @param string|null $format The format.
     * @param string|null $timeZone The time zone.
     * @param string|null $locale The locale.
     * @return string The time string.
     */
    public function time(DateTime $value, string|null $format = null, string|null $timeZone = null, string|null $locale = null): string
    {
        $locale ??= $this->getDefaultLocale();
        $format ??= $this->getBestDateFormat($locale, 'jmm');

        return $this->datetime($value, $format, $timeZone, $locale);
    }

    /**
     * Returns the best date format.
     *
     * @param string $locale The locale.
     * @param string $skeleton The skeleton format.
     * @return string The best date format.
     */
    protected function getBestDateFormat(string $locale, string $skeleton): string
    {
        return $this->dateFormats[$locale.'-'.$skeleton] = $this->getDatePatternGenerator($locale)->getBestPattern($skeleton) ?: $skeleton;
    }

    /**
     * Gets an IntlDatePatternGenerator for a locale.
     *
     * @param string $locale The locale.
     * @return IntlDatePatternGenerator The IntlDatePatternGenerator instance.
     */
    protected function getDatePatternGenerator(string $locale): IntlDatePatternGenerator
    {
        return $this->datePatternGenerators[$locale] ??= new IntlDatePatternGenerator($locale);
    }

    /**
     * Gets an IntlListFormatter for a locale.
     *
     * @param string $locale The locale.
     * @param int $type The type.
     * @param int $width The width.
     * @return IntlListFormatter The IntlListFormatter instance.
     */
    protected function getListFormatter(string $locale, int $type = IntlListFormatter::TYPE_AND, int $width = IntlListFormatter::WIDTH_WIDE): IntlListFormatter
    {
        return $this->listFormatters[$locale.'-'.$type.'-'.$width] ??= new IntlListFormatter($locale, $type, $width);
    }

    /**
     * Gets a NumberFormatter for a locale.
     *
     * @param string $locale The locale.
     * @param int $type The type.
     * @return NumberFormatter The NumberFormatter instance.
     */
    protected function getNumberFormatter(string $locale, int $type = NumberFormatter::DEFAULT_STYLE): NumberFormatter
    {
        $this->numberFormatters[$locale] ??= [];

        return $this->numberFormatters[$locale][$type] ??= new NumberFormatter($locale, $type);
    }
}
