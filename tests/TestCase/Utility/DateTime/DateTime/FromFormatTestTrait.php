<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use DateMalformedStringException;
use Fyre\Utility\DateTime\DateTime;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @phpstan-type FromFormatAccessor 'getDate'|'getDayOfYear'|'getHours'|'getMilliseconds'|'getMinutes'|'getMonth'|'getQuarter'|'getSeconds'|'getWeek'|'getWeekDay'|'getWeekDayInMonth'|'getWeekOfMonth'|'getWeekYear'|'getYear'|'toIsoString'
 */
trait FromFormatTestTrait
{
    /**
     * @return array<string, array{string, string, string, int|string}>
     */
    public static function fromFormatProvider(): array
    {
        return [
            '11 hour 1 digit' => ['K', '11', 'getHours', 11],
            '11 hour 1 digit padding' => ['K', '0', 'getHours', 0],
            '11 hour 2 digits' => ['KK', '11', 'getHours', 11],
            '11 hour 2 digits padding' => ['KK', '00', 'getHours', 00],
            '12 hour 1 digit' => ['h', '12', 'getHours', 0],
            '12 hour 1 digit padding' => ['h', '1', 'getHours', 1],
            '12 hour 2 digits' => ['hh', '12', 'getHours', 0],
            '12 hour 2 digits padding' => ['hh', '01', 'getHours', 1],
            '23 hour 1 digit' => ['H', '23', 'getHours', 23],
            '23 hour 1 digit padding' => ['H', '0', 'getHours', 0],
            '23 hour 2 digits' => ['HH', '23', 'getHours', 23],
            '23 hour 2 digits padding' => ['HH', '00', 'getHours', 0],
            '24 hour 1 digit' => ['k', '24', 'getHours', 0],
            '24 hour 1 digit padding' => ['k', '1', 'getHours', 1],
            '24 hour 2 digits' => ['kk', '24', 'getHours', 0],
            '24 hour 2 digits padding' => ['kk', '01', 'getHours', 1],
            'alt week day long' => ['EEEE', 'Friday', 'getWeekDay', 6],
            'alt week day short' => ['EEE', 'Fri', 'getWeekDay', 6],
            'day of month 1 digit' => ['d', '1', 'getDate', 1],
            'day of month 1 digit full' => ['d', '21', 'getDate', 21],
            'day of month 2 digits' => ['dd', '01', 'getDate', 1],
            'day of month 2 digits full' => ['dd', '21', 'getDate', 21],
            'day of week in month month' => ['F', '3', 'getWeekDayInMonth', 3],
            'day of year 1 digit' => ['D', '1', 'getDayOfYear', 1],
            'day of year 1 digit full' => ['D', '152', 'getDayOfYear', 152],
            'day of year 2 digits' => ['DD', '01', 'getDayOfYear', 1],
            'day of year 2 digits full' => ['DD', '152', 'getDayOfYear', 152],
            'day of year 3 digits' => ['DDD', '001', 'getDayOfYear', 1],
            'day of year 3 digits full' => ['DDD', '152', 'getDayOfYear', 152],
            'day period long' => ['aaaa', 'AM', 'getHours', 0],
            'day period long pm' => ['aaaa', 'PM', 'getHours', 12],
            // 'standalone week day narrow' => ['ccccc', 'F', 'getWeekDay', 6],
            'day period short' => ['aaa', 'AM', 'getHours', 0],
            'day period short pm' => ['aaa', 'PM', 'getHours', 12],
            'era long' => ['yyyy GGGG', '1970 Anno Domini', 'getYear', 1970],
            'era long bc' => ['yyyy GGGG', '1970 Before Christ', 'getYear', -1970],
            'era narrow' => ['yyyy GGGGG', '1970 A', 'getYear', 1970],
            'era narrow bc' => ['yyyy GGGGG', '1970 B', 'getYear', -1970],
            'era short' => ['yyyy GGG', '1970 AD', 'getYear', 1970],
            'era short bc' => ['yyyy GGG', '1970 BC', 'getYear', -1970],
            'fractional' => ['SSS', '123', 'getMilliseconds', 0],
            'minute 1 digit' => ['m', '25', 'getMinutes', 25],
            'minute 1 digit padding' => ['m', '1', 'getMinutes', 1],
            'minute 2 digits' => ['mm', '25', 'getMinutes', 25],
            'minute 2 digits padding' => ['mm', '01', 'getMinutes', 1],
            'month 1 digit' => ['M', '1', 'getMonth', 1],
            'month 1 digit full' => ['M', '10', 'getMonth', 10],
            'month 2 digits' => ['MM', '01', 'getMonth', 1],
            'month 2 digits full' => ['MM', '10', 'getMonth', 10],
            'month long' => ['MMMM', 'October', 'getMonth', 10],
            'month short' => ['MMM', 'Oct', 'getMonth', 10],
            'quarter 1 digit' => ['q', '3', 'getQuarter', 3],
            'quarter 2 digits' => ['qq', '03', 'getQuarter', 3],
            'second 1 digit' => ['s', '25', 'getSeconds', 25],
            'second 1 digit padding' => ['s', '1', 'getSeconds', 1],
            'second 2 digits' => ['ss', '25', 'getSeconds', 25],
            'second 2 digits padding' => ['ss', '01', 'getSeconds', 1],
            'standalone month 1 digit' => ['L', '1', 'getMonth', 1],
            // 'month narrow' => ['MMMMM', 'O', 'getMonth', 10],
            'standalone month 1 digit full' => ['L', '10', 'getMonth', 10],
            'standalone month 2 digits' => ['LL', '01', 'getMonth', 1],
            'standalone month 2 digits full' => ['LL', '10', 'getMonth', 10],
            'standalone month long' => ['LLLL', 'October', 'getMonth', 10],
            'standalone month short' => ['LLL', 'Oct', 'getMonth', 10],
            'standalone quarter 1 digit' => ['Q', '3', 'getQuarter', 3],
            'standalone quarter 2 digits' => ['QQ', '03', 'getQuarter', 3],
            // 'week day narrow' => ['eeeee', 'F', 'getWeekDay', 6],
            'standalone week day 1 digit' => ['c', '6', 'getWeekDay', 6],
            'standalone week day 2 digits' => ['cc', '06', 'getWeekDay', 6],
            'standalone week day long' => ['cccc', 'Friday', 'getWeekDay', 6],
            'standalone week day short' => ['ccc', 'Fri', 'getWeekDay', 6],
            'time zone ISO 8601 basic' => ['dd/MM/yyyy HH:mm:ss xx', '01/01/2019 00:00:00 +0000', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone ISO 8601 basic alt' => ['dd/MM/yyyy HH:mm:ss ZZZ', '01/01/2019 00:00:00 +0000', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone ISO 8601 basic alt time zone' => ['dd/MM/yyyy HH:mm:ss ZZZ', '01/01/2019 00:00:00 +1000', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone ISO 8601 basic short' => ['dd/MM/yyyy HH:mm:ss x', '01/01/2019 00:00:00 +00', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone ISO 8601 basic short time zone' => ['dd/MM/yyyy HH:mm:ss x', '01/01/2019 00:00:00 +10', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone ISO 8601 basic short z' => ['dd/MM/yyyy HH:mm:ss X', '01/01/2019 00:00:00 Z', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone ISO 8601 basic short z time zone' => ['dd/MM/yyyy HH:mm:ss X', '01/01/2019 00:00:00 +10', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone ISO 8601 basic time zone' => ['dd/MM/yyyy HH:mm:ss xx', '01/01/2019 00:00:00 +1000', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone ISO 8601 basic z' => ['dd/MM/yyyy HH:mm:ss XX', '01/01/2019 00:00:00 Z', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone ISO 8601 basic z time zone' => ['dd/MM/yyyy HH:mm:ss XX', '01/01/2019 00:00:00 +1000', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone ISO 8601 extended' => ['dd/MM/yyyy HH:mm:ss xxx', '01/01/2019 00:00:00 +00:00', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone ISO 8601 extended alt' => ['dd/MM/yyyy HH:mm:ss ZZZZZ', '01/01/2019 00:00:00 +00:00', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone ISO 8601 extended alt time zone' => ['dd/MM/yyyy HH:mm:ss ZZZZZ', '01/01/2019 00:00:00 +10:00', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone ISO 8601 extended time zone' => ['dd/MM/yyyy HH:mm:ss xxx', '01/01/2019 00:00:00 +10:00', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone ISO 8601 extended z' => ['dd/MM/yyyy HH:mm:ss XXX', '01/01/2019 00:00:00 Z', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone ISO 8601 extended z time zone' => ['dd/MM/yyyy HH:mm:ss XXX', '01/01/2019 00:00:00 +10:00', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone long basic' => ['dd/MM/yyyy HH:mm:ss ZZZZ', '01/01/2019 00:00:00 GMT+00:00', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone long basic time zone' => ['dd/MM/yyyy HH:mm:ss ZZZZ', '01/01/2019 00:00:00 GMT+10:00', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone long localized' => ['dd/MM/yyyy HH:mm:ss OOOO', '01/01/2019 00:00:00 GMT+00:00', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone long localized time zone' => ['dd/MM/yyyy HH:mm:ss OOOO', '01/01/2019 00:00:00 GMT+10:00', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone long time zone ID' => ['dd/MM/yyyy HH:mm:ss VV', '01/01/2019 00:00:00 UTC', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone long time zone ID time zone' => ['dd/MM/yyyy HH:mm:ss VV', '01/01/2019 00:00:00 Australia/Brisbane', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'time zone short localized' => ['dd/MM/yyyy HH:mm:ss O', '01/01/2019 00:00:00 GMT+00', 'toIsoString', '2019-01-01T00:00:00.000+00:00'],
            'time zone short localized time zone' => ['dd/MM/yyyy HH:mm:ss O', '01/01/2019 00:00:00 GMT+10', 'toIsoString', '2018-12-31T14:00:00.000+00:00'],
            'week 1 digit' => ['w', '1', 'getWeek', 1],
            // 'standalone month narrow' => ['LLLLL', 'O', 'getMonth', 10],
            'week 1 digit full' => ['w', '22', 'getWeek', 22],
            'week 2 digits' => ['ww', '01', 'getWeek', 1],
            'week 2 digits full' => ['ww', '22', 'getWeek', 22],
            // 'alt week day narrow' => ['EEEEE', 'F', 'getWeekDay', 6],
            'week day 1 digit' => ['e', '6', 'getWeekDay', 6],
            'week day 2 digits' => ['ee', '06', 'getWeekDay', 6],
            'week day long' => ['eeee', 'Friday', 'getWeekDay', 6],
            'week day short' => ['eee', 'Fri', 'getWeekDay', 6],
            'week of month' => ['W', '3', 'getWeekOfMonth', 3],
            'week year 1 digit' => ['Y w e', '5 1 1', 'getWeekYear', 5],
            'week year 1 digit full' => ['Y w e', '2018 1 1', 'getWeekYear', 2018],
            'week year 2 digits' => ['YY w e', '88 1 1', 'getWeekYear', 1988],
            'week year 2 digits full' => ['YY w e', '2018 1 1', 'getWeekYear', 2018],
            'week year 3 digits' => ['YYY w e', '088 1 1', 'getWeekYear', 88],
            'week year 3 digits full' => ['YYY w e', '2018 1 1', 'getWeekYear', 2018],
            'week year 4 digits' => ['YYYY w e', '0088 1 1', 'getWeekYear', 88],
            'week year 4 digits full' => ['YYYY w e', '2018 1 1', 'getWeekYear', 2018],
            'year 1 digit' => ['y', '5', 'getYear', 5],
            'year 1 digit full' => ['y', '2018', 'getYear', 2018],
            'year 2 digits' => ['yy', '88', 'getYear', 1988],
            'year 2 digits full' => ['yy', '2018', 'getYear', 2018],
            'year 3 digits' => ['yyy', '088', 'getYear', 88],
            'year 3 digits full' => ['yyy', '2018', 'getYear', 2018],
            'year 4 digits' => ['yyyy', '0088', 'getYear', 88],
            'year 4 digits full' => ['yyyy', '2018', 'getYear', 2018],
        ];
    }

    /**
     * @param FromFormatAccessor $accessor
     */
    #[DataProvider('fromFormatProvider')]
    public function testCreateFromFormat(
        string $format,
        string $value,
        string $accessor,
        int|string $expected
    ): void {
        $date = DateTime::createFromFormat($format, $value);

        $this->assertSame(
            $expected,
            $date->$accessor()
        );
    }

    public function testCreateFromFormatInvalid(): void
    {
        $this->expectException(DateMalformedStringException::class);
        $this->expectExceptionMessage('Date parsing failed: U_PARSE_ERROR');
        $this->expectExceptionCode(9);

        DateTime::createFromFormat('yyyy', 'a');
    }
}
