<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @phpstan-type FromFormatLocaleAccessor 'getDate'|'getDayOfYear'|'getHours'|'getMilliseconds'|'getMinutes'|'getMonth'|'getQuarter'|'getSeconds'|'getWeek'|'getWeekDay'|'getWeekDayInMonth'|'getWeekOfMonth'|'getWeekYear'|'getYear'|'toIsoString'
 */
trait FromFormatLocaleTestTrait
{
    /**
     * @return array<string, array{string, string, string, string, int|string}>
     */
    public static function fromFormatLocaleProvider(): array
    {
        return [
            '11 hour 1 digit' => ['K', '١١', 'ar-eg', 'getHours', 11],
            '11 hour 1 digit padding' => ['K', '٠', 'ar-eg', 'getHours', 0],
            '11 hour 2 digits' => ['KK', '١١', 'ar-eg', 'getHours', 11],
            '11 hour 2 digits padding' => ['KK', '٠٠', 'ar-eg', 'getHours', 00],
            '12 hour 1 digit' => ['h', '١٢', 'ar-eg', 'getHours', 0],
            '12 hour 1 digit padding' => ['h', '١', 'ar-eg', 'getHours', 1],
            '12 hour 2 digits' => ['hh', '١٢', 'ar-eg', 'getHours', 0],
            '12 hour 2 digits padding' => ['hh', '٠١', 'ar-eg', 'getHours', 1],
            '23 hour 1 digit' => ['H', '٢٣', 'ar-eg', 'getHours', 23],
            '23 hour 1 digit padding' => ['H', '٠', 'ar-eg', 'getHours', 0],
            '23 hour 2 digits' => ['HH', '٢٣', 'ar-eg', 'getHours', 23],
            '23 hour 2 digits padding' => ['HH', '٠٠', 'ar-eg', 'getHours', 0],
            '24 hour 1 digit' => ['k', '٢٤', 'ar-eg', 'getHours', 0],
            '24 hour 1 digit padding' => ['k', '١', 'ar-eg', 'getHours', 1],
            '24 hour 2 digits' => ['kk', '٢٤', 'ar-eg', 'getHours', 0],
            '24 hour 2 digits padding' => ['kk', '٠١', 'ar-eg', 'getHours', 1],
            'alt week day long' => ['EEEE', 'пятница', 'ru', 'getWeekDay', 5],
            'alt week day short' => ['EEE', 'пт', 'ru', 'getWeekDay', 5],
            'day of month 1 digit' => ['d', '١', 'ar-eg', 'getDate', 1],
            'day of month 1 digit full' => ['d', '٢١', 'ar-eg', 'getDate', 21],
            'day of month 2 digits' => ['dd', '٠١', 'ar-eg', 'getDate', 1],
            'day of month 2 digits full' => ['dd', '٢١', 'ar-eg', 'getDate', 21],
            'day of week in month month' => ['F', '٣', 'ar-eg', 'getWeekDayInMonth', 3],
            'day of year 1 digit' => ['D', '١', 'ar-eg', 'getDayOfYear', 1],
            'day of year 1 digit full' => ['D', '١٥٢', 'ar-eg', 'getDayOfYear', 152],
            'day of year 2 digits' => ['DD', '٠١', 'ar-eg', 'getDayOfYear', 1],
            'day of year 2 digits full' => ['DD', '١٥٢', 'ar-eg', 'getDayOfYear', 152],
            'day of year 3 digits' => ['DDD', '٠٠١', 'ar-eg', 'getDayOfYear', 1],
            'day of year 3 digits full' => ['DDD', '١٥٢', 'ar-eg', 'getDayOfYear', 152],
            'day period long' => ['aaaa', '上午', 'zh', 'getHours', 0],
            'day period long pm' => ['aaaa', '下午', 'zh', 'getHours', 12],
            // 'standalone week day narrow' => ['ccccc', 'П', 'ru', 'getWeekDay', 1],
            'day period short' => ['aaa', '上午', 'zh', 'getHours', 0],
            'day period short pm' => ['aaa', '下午', 'zh', 'getHours', 12],
            'era long' => ['yyyy GGGG', '1970 от Рождества Христова', 'ru', 'getYear', 1970],
            'era long bc' => ['yyyy GGGG', '1970 до Рождества Христова', 'ru', 'getYear', -1970],
            'era narrow' => ['yyyy GGGGG', '1970 н.э.', 'ru', 'getYear', 1970],
            'era narrow bc' => ['yyyy GGGGG', '1970 до н.э.', 'ru', 'getYear', -1970],
            'era short' => ['yyyy GGG', '1970 н. э.', 'ru', 'getYear', 1970],
            'era short bc' => ['yyyy GGG', '1970 до н. э.', 'ru', 'getYear', -1970],
            'fractional' => ['SSS', '١٢٣', 'ar-eg', 'getMilliseconds', 0],
            'minute 1 digit' => ['m', '٢٥', 'ar-eg', 'getMinutes', 25],
            'minute 1 digit padding' => ['m', '١', 'ar-eg', 'getMinutes', 1],
            'minute 2 digits' => ['mm', '٢٥', 'ar-eg', 'getMinutes', 25],
            'minute 2 digits padding' => ['mm', '٠١', 'ar-eg', 'getMinutes', 1],
            'month 1 digit' => ['M', '١', 'ar-eg', 'getMonth', 1],
            'month 1 digit full' => ['M', '١٠', 'ar-eg', 'getMonth', 10],
            'month 2 digits' => ['MM', '٠١', 'ar-eg', 'getMonth', 1],
            'month 2 digits full' => ['MM', '١٠', 'ar-eg', 'getMonth', 10],
            'month long' => ['MMMM', 'октября', 'ru', 'getMonth', 10],
            'month short' => ['MMM', 'окт.', 'ru', 'getMonth', 10],
            'quarter 1 digit' => ['q', '٣', 'ar-eg', 'getQuarter', 3],
            'quarter 2 digits' => ['qq', '٠٣', 'ar-eg', 'getQuarter', 3],
            'second 1 digit' => ['s', '٢٥', 'ar-eg', 'getSeconds', 25],
            'second 1 digit padding' => ['s', '١', 'ar-eg', 'getSeconds', 1],
            'second 2 digits' => ['ss', '٢٥', 'ar-eg', 'getSeconds', 25],
            'second 2 digits padding' => ['ss', '٠١', 'ar-eg', 'getSeconds', 1],
            'standalone month 1 digit' => ['L', '١', 'ar-eg', 'getMonth', 1],
            // 'month narrow' => ['MMMMM', 'О', 'ru', 'getMonth', 10],
            'standalone month 1 digit full' => ['L', '١٠', 'ar-eg', 'getMonth', 10],
            'standalone month 2 digits' => ['LL', '٠١', 'ar-eg', 'getMonth', 1],
            'standalone month 2 digits full' => ['LL', '١٠', 'ar-eg', 'getMonth', 10],
            'standalone month long' => ['LLLL', 'октябрь', 'ru', 'getMonth', 10],
            'standalone month short' => ['LLL', 'окт.', 'ru', 'getMonth', 10],
            'standalone quarter 1 digit' => ['Q', '٣', 'ar-eg', 'getQuarter', 3],
            'standalone quarter 2 digits' => ['QQ', '٠٣', 'ar-eg', 'getQuarter', 3],
            // 'week day narrow' => ['eeeee', 'П', 'ru', 'getWeekDay', 1],
            'standalone week day 1 digit' => ['c', '٥', 'ar-eg', 'getWeekDay', 5],
            'standalone week day 2 digits' => ['cc', '٠٥', 'ar-eg', 'getWeekDay', 5],
            'standalone week day long' => ['cccc', 'пятница', 'ru', 'getWeekDay', 5],
            'standalone week day short' => ['ccc', 'пт', 'ru', 'getWeekDay', 5],
            'time zone ISO 8601 basic' => ['dd/MM/yyyy HH:mm:ss xx', '01/01/2019 00:00:00 +0000', 'ru', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone ISO 8601 basic alt' => ['dd/MM/yyyy HH:mm:ss ZZZ', '01/01/2019 00:00:00 +0000', 'ru', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone ISO 8601 basic alt time zone' => ['dd/MM/yyyy HH:mm:ss ZZZ', '01/01/2019 00:00:00 +1000', 'ru', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone ISO 8601 basic short' => ['dd/MM/yyyy HH:mm:ss x', '01/01/2019 00:00:00 +00', 'ru', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone ISO 8601 basic short time zone' => ['dd/MM/yyyy HH:mm:ss x', '01/01/2019 00:00:00 +10', 'ru', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone ISO 8601 basic short z' => ['dd/MM/yyyy HH:mm:ss X', '01/01/2019 00:00:00 Z', 'ru', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone ISO 8601 basic short z time zone' => ['dd/MM/yyyy HH:mm:ss X', '01/01/2019 00:00:00 +10', 'ru', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone ISO 8601 basic time zone' => ['dd/MM/yyyy HH:mm:ss xx', '01/01/2019 00:00:00 +1000', 'ru', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone ISO 8601 basic z' => ['dd/MM/yyyy HH:mm:ss XX', '01/01/2019 00:00:00 Z', 'ru', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone ISO 8601 basic z time zone' => ['dd/MM/yyyy HH:mm:ss XX', '01/01/2019 00:00:00 +1000', 'ru', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone ISO 8601 extended' => ['dd/MM/yyyy HH:mm:ss xxx', '01/01/2019 00:00:00 +00:00', 'ru', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone ISO 8601 extended alt' => ['dd/MM/yyyy HH:mm:ss ZZZZZ', '01/01/2019 00:00:00 +00:00', 'ru', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone ISO 8601 extended alt time zone' => ['dd/MM/yyyy HH:mm:ss ZZZZZ', '01/01/2019 00:00:00 +10:00', 'ru', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone ISO 8601 extended time zone' => ['dd/MM/yyyy HH:mm:ss xxx', '01/01/2019 00:00:00 +10:00', 'ru', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone ISO 8601 extended z' => ['dd/MM/yyyy HH:mm:ss XXX', '01/01/2019 00:00:00 Z', 'ru', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone ISO 8601 extended z time zone' => ['dd/MM/yyyy HH:mm:ss XXX', '01/01/2019 00:00:00 +10:00', 'ru', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone long basic' => ['dd/MM/yyyy HH:mm:ss ZZZZ', '01/01/2019 00:00:00 GMT+00:00', 'ru', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone long basic time zone' => ['dd/MM/yyyy HH:mm:ss ZZZZ', '01/01/2019 00:00:00 GMT+10:00', 'ru', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone long localized' => ['dd/MM/yyyy HH:mm:ss OOOO', '01/01/2019 00:00:00 GMT+00:00', 'ru', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone long localized time zone' => ['dd/MM/yyyy HH:mm:ss OOOO', '01/01/2019 00:00:00 GMT+10:00', 'ru', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone long time zone ID' => ['dd/MM/yyyy HH:mm:ss VV', '01/01/2019 00:00:00 UTC', 'ru', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone long time zone ID time zone' => ['dd/MM/yyyy HH:mm:ss VV', '01/01/2019 00:00:00 Australia/Brisbane', 'ru', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone short localized' => ['dd/MM/yyyy HH:mm:ss O', '01/01/2019 00:00:00 GMT+00', 'ru', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone short localized time zone' => ['dd/MM/yyyy HH:mm:ss O', '01/01/2019 00:00:00 GMT+10', 'ru', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'week 1 digit' => ['w', '١', 'ar-eg', 'getWeek', 1],
            // 'standalone month narrow' => ['LLLLL', 'О', 'ru', 'getMonth', 10],
            'week 1 digit full' => ['w', '٢٢', 'ar-eg', 'getWeek', 22],
            'week 2 digits' => ['ww', '٠١', 'ar-eg', 'getWeek', 1],
            'week 2 digits full' => ['ww', '٢٢', 'ar-eg', 'getWeek', 22],
            // 'alt week day narrow' => ['EEEEE', 'П', 'ru', 'getWeekDay', 1],
            'week day 1 digit' => ['e', '٥', 'ar-eg', 'getWeekDay', 5],
            'week day 2 digits' => ['ee', '٠٥', 'ar-eg', 'getWeekDay', 5],
            'week day long' => ['eeee', 'пятница', 'ru', 'getWeekDay', 5],
            'week day short' => ['eee', 'пт', 'ru', 'getWeekDay', 5],
            'week of month' => ['W', '٣', 'ar-eg', 'getWeekOfMonth', 3],
            'week year 1 digit' => ['Y w e', '٥ ١ ١', 'ar-eg', 'getWeekYear', 5],
            'week year 1 digit full' => ['Y w e', '٢٠١٨ ١ ٣', 'ar-eg', 'getWeekYear', 2018],
            'week year 2 digits' => ['YY w e', '٨٨ ١ ٦', 'ar-eg', 'getWeekYear', 1988],
            'week year 2 digits full' => ['YY w e', '٢٠١٨ ١ ٣', 'ar-eg', 'getWeekYear', 2018],
            'week year 3 digits' => ['YYY w e', '٠٨٨ ١ ٦', 'ar-eg', 'getWeekYear', 88],
            'week year 3 digits full' => ['YYY w e', '٢٠١٨ ١ ٣', 'ar-eg', 'getWeekYear', 2018],
            'week year 4 digits' => ['YYYY w e', '٠٠٨٨ ١ ٦', 'ar-eg', 'getWeekYear', 88],
            'week year 4 digits full' => ['YYYY w e', '٢٠١٨ ١ ٣', 'ar-eg', 'getWeekYear', 2018],
            'year 1 digit' => ['y', '٥', 'ar-eg', 'getYear', 5],
            'year 1 digit full' => ['y', '٢٠١٨', 'ar-eg', 'getYear', 2018],
            'year 2 digits' => ['yy', '٨٨', 'ar-eg', 'getYear', 1988],
            'year 2 digits full' => ['yy', '٢٠١٨', 'ar-eg', 'getYear', 2018],
            'year 3 digits' => ['yyy', '٠٨٨', 'ar-eg', 'getYear', 88],
            'year 3 digits full' => ['yyy', '٢٠١٨', 'ar-eg', 'getYear', 2018],
            'year 4 digits' => ['yyyy', '٠٠٨٨', 'ar-eg', 'getYear', 88],
            'year 4 digits full' => ['yyyy', '٢٠١٨', 'ar-eg', 'getYear', 2018],
        ];
    }

    /**
     * @param FromFormatLocaleAccessor $accessor
     */
    #[DataProvider('fromFormatLocaleProvider')]
    public function testCreateFromFormatLocale(
        string $format,
        string $value,
        string $locale,
        string $accessor,
        int|string $expected
    ): void {
        $date = DateTime::createFromFormat($format, $value, locale: $locale);

        $this->assertSame(
            $expected,
            $date->$accessor()
        );
    }
}
