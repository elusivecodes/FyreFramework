<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use DateMalformedStringException;
use Fyre\Utility\DateTime\DateTime;

trait FromFormatTestTrait
{
    public function testCreateFromFormat11Hour1Digit(): void
    {
        $this->assertSame(
            11,
            DateTime::createFromFormat('K', '11')->getHours()
        );
    }

    public function testCreateFromFormat11Hour1DigitPadding(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('K', '0')->getHours()
        );
    }

    public function testCreateFromFormat11Hour2Digits(): void
    {
        $this->assertSame(
            11,
            DateTime::createFromFormat('KK', '11')->getHours()
        );
    }

    public function testCreateFromFormat11Hour2DigitsPadding(): void
    {
        $this->assertSame(
            00,
            DateTime::createFromFormat('KK', '00')->getHours()
        );
    }

    public function testCreateFromFormat12Hour1Digit(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('h', '12')->getHours()
        );
    }

    public function testCreateFromFormat12Hour1DigitPadding(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('h', '1')->getHours()
        );
    }

    public function testCreateFromFormat12Hour2Digits(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('hh', '12')->getHours()
        );
    }

    public function testCreateFromFormat12Hour2DigitsPadding(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('hh', '01')->getHours()
        );
    }

    public function testCreateFromFormat23Hour1Digit(): void
    {
        $this->assertSame(
            23,
            DateTime::createFromFormat('H', '23')->getHours()
        );
    }

    public function testCreateFromFormat23Hour1DigitPadding(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('H', '0')->getHours()
        );
    }

    public function testCreateFromFormat23Hour2Digits(): void
    {
        $this->assertSame(
            23,
            DateTime::createFromFormat('HH', '23')->getHours()
        );
    }

    public function testCreateFromFormat23Hour2DigitsPadding(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('HH', '00')->getHours()
        );
    }

    public function testCreateFromFormat24Hour1Digit(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('k', '24')->getHours()
        );
    }

    public function testCreateFromFormat24Hour1DigitPadding(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('k', '1')->getHours()
        );
    }

    public function testCreateFromFormat24Hour2Digits(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('kk', '24')->getHours()
        );
    }

    public function testCreateFromFormat24Hour2DigitsPadding(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('kk', '01')->getHours()
        );
    }

    public function testCreateFromFormatAltWeekDayLong(): void
    {
        $this->assertSame(
            6,
            DateTime::createFromFormat('EEEE', 'Friday')->getWeekDay()
        );
    }

    public function testCreateFromFormatAltWeekDayShort(): void
    {
        $this->assertSame(
            6,
            DateTime::createFromFormat('EEE', 'Fri')->getWeekDay()
        );
    }

    public function testCreateFromFormatDayOfMonth1Digit(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('d', '1')->getDate()
        );
    }

    public function testCreateFromFormatDayOfMonth1DigitFull(): void
    {
        $this->assertSame(
            21,
            DateTime::createFromFormat('d', '21')->getDate()
        );
    }

    public function testCreateFromFormatDayOfMonth2Digits(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('dd', '01')->getDate()
        );
    }

    public function testCreateFromFormatDayOfMonth2DigitsFull(): void
    {
        $this->assertSame(
            21,
            DateTime::createFromFormat('dd', '21')->getDate()
        );
    }

    public function testCreateFromFormatDayOfWeekInMonthMonth(): void
    {
        $this->assertSame(
            3,
            DateTime::createFromFormat('F', '3')->getWeekDayInMonth()
        );
    }

    public function testCreateFromFormatDayOfYear1Digit(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('D', '1')->getDayOfYear()
        );
    }

    public function testCreateFromFormatDayOfYear1DigitFull(): void
    {
        $this->assertSame(
            152,
            DateTime::createFromFormat('D', '152')->getDayOfYear()
        );
    }

    public function testCreateFromFormatDayOfYear2Digits(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('DD', '01')->getDayOfYear()
        );
    }

    public function testCreateFromFormatDayOfYear2DigitsFull(): void
    {
        $this->assertSame(
            152,
            DateTime::createFromFormat('DD', '152')->getDayOfYear()
        );
    }

    public function testCreateFromFormatDayOfYear3Digits(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('DDD', '001')->getDayOfYear()
        );
    }

    public function testCreateFromFormatDayOfYear3DigitsFull(): void
    {
        $this->assertSame(
            152,
            DateTime::createFromFormat('DDD', '152')->getDayOfYear()
        );
    }

    public function testCreateFromFormatDayPeriodLong(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('aaaa', 'AM')->getHours()
        );
    }

    public function testCreateFromFormatDayPeriodLongPm(): void
    {
        $this->assertSame(
            12,
            DateTime::createFromFormat('aaaa', 'PM')->getHours()
        );
    }

    // public function testCreateFromFormatStandaloneWeekDayNarrow(): void
    // {
    //     $this->assertSame(
    //         6,
    //         DateTime::createFromFormat('ccccc', 'F')->getWeekDay()
    //     );
    // }

    public function testCreateFromFormatDayPeriodShort(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('aaa', 'AM')->getHours()
        );
    }

    public function testCreateFromFormatDayPeriodShortPm(): void
    {
        $this->assertSame(
            12,
            DateTime::createFromFormat('aaa', 'PM')->getHours()
        );
    }

    public function testCreateFromFormatEraLong(): void
    {
        $this->assertSame(
            1970,
            DateTime::createFromFormat('yyyy GGGG', '1970 Anno Domini')->getYear()
        );
    }

    public function testCreateFromFormatEraLongBc(): void
    {
        $this->assertSame(
            -1970,
            DateTime::createFromFormat('yyyy GGGG', '1970 Before Christ')->getYear()
        );
    }

    public function testCreateFromFormatEraNarrow(): void
    {
        $this->assertSame(
            1970,
            DateTime::createFromFormat('yyyy GGGGG', '1970 A')->getYear()
        );
    }

    public function testCreateFromFormatEraNarrowBc(): void
    {
        $this->assertSame(
            -1970,
            DateTime::createFromFormat('yyyy GGGGG', '1970 B')->getYear()
        );
    }

    public function testCreateFromFormatEraShort(): void
    {
        $this->assertSame(
            1970,
            DateTime::createFromFormat('yyyy GGG', '1970 AD')->getYear()
        );
    }

    public function testCreateFromFormatEraShortBc(): void
    {
        $this->assertSame(
            -1970,
            DateTime::createFromFormat('yyyy GGG', '1970 BC')->getYear()
        );
    }

    public function testCreateFromFormatFractional(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('SSS', '123')->getMilliseconds()
        );
    }

    public function testCreateFromFormatInvalid(): void
    {
        $this->expectException(DateMalformedStringException::class);
        $this->expectExceptionMessage('Date parsing failed: U_PARSE_ERROR');
        $this->expectExceptionCode(9);

        DateTime::createFromFormat('yyyy', 'a');
    }

    public function testCreateFromFormatMinute1Digit(): void
    {
        $this->assertSame(
            25,
            DateTime::createFromFormat('m', '25')->getMinutes()
        );
    }

    public function testCreateFromFormatMinute1DigitPadding(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('m', '1')->getMinutes()
        );
    }

    public function testCreateFromFormatMinute2Digits(): void
    {
        $this->assertSame(
            25,
            DateTime::createFromFormat('mm', '25')->getMinutes()
        );
    }

    public function testCreateFromFormatMinute2DigitsPadding(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('mm', '01')->getMinutes()
        );
    }

    public function testCreateFromFormatMonth1Digit(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('M', '1')->getMonth()
        );
    }

    public function testCreateFromFormatMonth1DigitFull(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromFormat('M', '10')->getMonth()
        );
    }

    public function testCreateFromFormatMonth2Digits(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('MM', '01')->getMonth()
        );
    }

    public function testCreateFromFormatMonth2DigitsFull(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromFormat('MM', '10')->getMonth()
        );
    }

    public function testCreateFromFormatMonthLong(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromFormat('MMMM', 'October')->getMonth()
        );
    }

    public function testCreateFromFormatMonthShort(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromFormat('MMM', 'Oct')->getMonth()
        );
    }

    public function testCreateFromFormatQuarter1Digit(): void
    {
        $this->assertSame(
            3,
            DateTime::createFromFormat('q', '3')->getQuarter()
        );
    }

    public function testCreateFromFormatQuarter2Digits(): void
    {
        $this->assertSame(
            3,
            DateTime::createFromFormat('qq', '03')->getQuarter()
        );
    }

    public function testCreateFromFormatSecond1Digit(): void
    {
        $this->assertSame(
            25,
            DateTime::createFromFormat('s', '25')->getSeconds()
        );
    }

    public function testCreateFromFormatSecond1DigitPadding(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('s', '1')->getSeconds()
        );
    }

    public function testCreateFromFormatSecond2Digits(): void
    {
        $this->assertSame(
            25,
            DateTime::createFromFormat('ss', '25')->getSeconds()
        );
    }

    public function testCreateFromFormatSecond2DigitsPadding(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('ss', '01')->getSeconds()
        );
    }

    public function testCreateFromFormatStandaloneMonth1Digit(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('L', '1')->getMonth()
        );
    }

    // public function testCreateFromFormatMonthNarrow(): void
    // {
    //     $this->assertSame(
    //         10,
    //         DateTime::createFromFormat('MMMMM', 'O')->getMonth()
    //     );
    // }

    public function testCreateFromFormatStandaloneMonth1DigitFull(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromFormat('L', '10')->getMonth()
        );
    }

    public function testCreateFromFormatStandaloneMonth2Digits(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('LL', '01')->getMonth()
        );
    }

    public function testCreateFromFormatStandaloneMonth2DigitsFull(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromFormat('LL', '10')->getMonth()
        );
    }

    public function testCreateFromFormatStandaloneMonthLong(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromFormat('LLLL', 'October')->getMonth()
        );
    }

    public function testCreateFromFormatStandaloneMonthShort(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromFormat('LLL', 'Oct')->getMonth()
        );
    }

    public function testCreateFromFormatStandaloneQuarter1Digit(): void
    {
        $this->assertSame(
            3,
            DateTime::createFromFormat('Q', '3')->getQuarter()
        );
    }

    public function testCreateFromFormatStandaloneQuarter2Digits(): void
    {
        $this->assertSame(
            3,
            DateTime::createFromFormat('QQ', '03')->getQuarter()
        );
    }

    // public function testCreateFromFormatWeekDayNarrow(): void
    // {
    //     $this->assertSame(
    //         6,
    //         DateTime::createFromFormat('eeeee', 'F')->getWeekDay()
    //     );
    // }

    public function testCreateFromFormatStandaloneWeekDay1Digit(): void
    {
        $this->assertSame(
            6,
            DateTime::createFromFormat('c', '6')->getWeekDay()
        );
    }

    public function testCreateFromFormatStandaloneWeekDay2Digits(): void
    {
        $this->assertSame(
            6,
            DateTime::createFromFormat('cc', '06')->getWeekDay()
        );
    }

    public function testCreateFromFormatStandaloneWeekDayLong(): void
    {
        $this->assertSame(
            6,
            DateTime::createFromFormat('cccc', 'Friday')->getWeekDay()
        );
    }

    public function testCreateFromFormatStandaloneWeekDayShort(): void
    {
        $this->assertSame(
            6,
            DateTime::createFromFormat('ccc', 'Fri')->getWeekDay()
        );
    }

    public function testCreateFromFormatTimeZoneIso8601Basic(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss xx', '01/01/2019 00:00:00 +0000')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneIso8601BasicAlt(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss ZZZ', '01/01/2019 00:00:00 +0000')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneIso8601BasicAltTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss ZZZ', '01/01/2019 00:00:00 +1000')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneIso8601BasicShort(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss x', '01/01/2019 00:00:00 +00')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneIso8601BasicShortTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss x', '01/01/2019 00:00:00 +10')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneIso8601BasicShortZ(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss X', '01/01/2019 00:00:00 Z')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneIso8601BasicShortZTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss X', '01/01/2019 00:00:00 +10')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneIso8601BasicTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss xx', '01/01/2019 00:00:00 +1000')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneIso8601BasicZ(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss XX', '01/01/2019 00:00:00 Z')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneIso8601BasicZTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss XX', '01/01/2019 00:00:00 +1000')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneIso8601Extended(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss xxx', '01/01/2019 00:00:00 +00:00')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneIso8601ExtendedAlt(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss ZZZZZ', '01/01/2019 00:00:00 +00:00')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneIso8601ExtendedAltTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss ZZZZZ', '01/01/2019 00:00:00 +10:00')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneIso8601ExtendedTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss xxx', '01/01/2019 00:00:00 +10:00')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneIso8601ExtendedZ(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss XXX', '01/01/2019 00:00:00 Z')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneIso8601ExtendedZTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss XXX', '01/01/2019 00:00:00 +10:00')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneLongBasic(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss ZZZZ', '01/01/2019 00:00:00 GMT+00:00')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneLongBasicTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss ZZZZ', '01/01/2019 00:00:00 GMT+10:00')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneLongLocalized(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss OOOO', '01/01/2019 00:00:00 GMT+00:00')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneLongLocalizedTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss OOOO', '01/01/2019 00:00:00 GMT+10:00')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneLongTimeZoneId(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss VV', '01/01/2019 00:00:00 UTC')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneLongTimeZoneIdTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss VV', '01/01/2019 00:00:00 Australia/Brisbane')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneShortLocalized(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss O', '01/01/2019 00:00:00 GMT+00')->toIsoString()
        );
    }

    public function testCreateFromFormatTimeZoneShortLocalizedTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss O', '01/01/2019 00:00:00 GMT+10')->toIsoString()
        );
    }

    public function testCreateFromFormatWeek1Digit(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('w', '1')->getWeek()
        );
    }

    // public function testCreateFromFormatStandaloneMonthNarrow(): void
    // {
    //     $this->assertSame(
    //         10,
    //         DateTime::createFromFormat('LLLLL', 'O')->getMonth()
    //     );
    // }

    public function testCreateFromFormatWeek1DigitFull(): void
    {
        $this->assertSame(
            22,
            DateTime::createFromFormat('w', '22')->getWeek()
        );
    }

    public function testCreateFromFormatWeek2Digits(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('ww', '01')->getWeek()
        );
    }

    public function testCreateFromFormatWeek2DigitsFull(): void
    {
        $this->assertSame(
            22,
            DateTime::createFromFormat('ww', '22')->getWeek()
        );
    }

    // public function testCreateFromFormatAltWeekDayNarrow(): void
    // {
    //     $this->assertSame(
    //         6,
    //         DateTime::createFromFormat('EEEEE', 'F')->getWeekDay()
    //     );
    // }

    public function testCreateFromFormatWeekDay1Digit(): void
    {
        $this->assertSame(
            6,
            DateTime::createFromFormat('e', '6')->getWeekDay()
        );
    }

    public function testCreateFromFormatWeekDay2Digits(): void
    {
        $this->assertSame(
            6,
            DateTime::createFromFormat('ee', '06')->getWeekDay()
        );
    }

    public function testCreateFromFormatWeekDayLong(): void
    {
        $this->assertSame(
            6,
            DateTime::createFromFormat('eeee', 'Friday')->getWeekDay()
        );
    }

    public function testCreateFromFormatWeekDayShort(): void
    {
        $this->assertSame(
            6,
            DateTime::createFromFormat('eee', 'Fri')->getWeekDay()
        );
    }

    public function testCreateFromFormatWeekOfMonth(): void
    {
        $this->assertSame(
            3,
            DateTime::createFromFormat('W', '3')->getWeekOfMonth()
        );
    }

    public function testCreateFromFormatWeekYear1Digit(): void
    {
        $this->assertSame(
            5,
            DateTime::createFromFormat('Y w e', '5 1 1')->getWeekYear()
        );
    }

    public function testCreateFromFormatWeekYear1DigitFull(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromFormat('Y w e', '2018 1 1')->getWeekYear()
        );
    }

    public function testCreateFromFormatWeekYear2Digits(): void
    {
        $this->assertSame(
            1988,
            DateTime::createFromFormat('YY w e', '88 1 1')->getWeekYear()
        );
    }

    public function testCreateFromFormatWeekYear2DigitsFull(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromFormat('YY w e', '2018 1 1')->getWeekYear()
        );
    }

    public function testCreateFromFormatWeekYear3Digits(): void
    {
        $this->assertSame(
            88,
            DateTime::createFromFormat('YYY w e', '088 1 1')->getWeekYear()
        );
    }

    public function testCreateFromFormatWeekYear3DigitsFull(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromFormat('YYY w e', '2018 1 1')->getWeekYear()
        );
    }

    public function testCreateFromFormatWeekYear4Digits(): void
    {
        $this->assertSame(
            88,
            DateTime::createFromFormat('YYYY w e', '0088 1 1')->getWeekYear()
        );
    }

    public function testCreateFromFormatWeekYear4DigitsFull(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromFormat('YYYY w e', '2018 1 1')->getWeekYear()
        );
    }

    public function testCreateFromFormatYear1Digit(): void
    {
        $this->assertSame(
            5,
            DateTime::createFromFormat('y', '5')->getYear()
        );
    }

    public function testCreateFromFormatYear1DigitFull(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromFormat('y', '2018')->getYear()
        );
    }

    public function testCreateFromFormatYear2Digits(): void
    {
        $this->assertSame(
            1988,
            DateTime::createFromFormat('yy', '88')->getYear()
        );
    }

    public function testCreateFromFormatYear2DigitsFull(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromFormat('yy', '2018')->getYear()
        );
    }

    public function testCreateFromFormatYear3Digits(): void
    {
        $this->assertSame(
            88,
            DateTime::createFromFormat('yyy', '088')->getYear()
        );
    }

    public function testCreateFromFormatYear3DigitsFull(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromFormat('yyy', '2018')->getYear()
        );
    }

    public function testCreateFromFormatYear4Digits(): void
    {
        $this->assertSame(
            88,
            DateTime::createFromFormat('yyyy', '0088')->getYear()
        );
    }

    public function testCreateFromFormatYear4DigitsFull(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromFormat('yyyy', '2018')->getYear()
        );
    }
}
