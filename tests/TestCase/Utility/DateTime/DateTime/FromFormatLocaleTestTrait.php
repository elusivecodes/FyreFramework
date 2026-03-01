<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;

trait FromFormatLocaleTestTrait
{
    public function testCreateFromFormatLocale11Hour1Digit(): void
    {
        $this->assertSame(
            11,
            DateTime::createFromFormat('K', '١١', null, 'ar-eg')->getHours()
        );
    }

    public function testCreateFromFormatLocale11Hour1DigitPadding(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('K', '٠', null, 'ar-eg')->getHours()
        );
    }

    public function testCreateFromFormatLocale11Hour2Digits(): void
    {
        $this->assertSame(
            11,
            DateTime::createFromFormat('KK', '١١', null, 'ar-eg')->getHours()
        );
    }

    public function testCreateFromFormatLocale11Hour2DigitsPadding(): void
    {
        $this->assertSame(
            00,
            DateTime::createFromFormat('KK', '٠٠', null, 'ar-eg')->getHours()
        );
    }

    public function testCreateFromFormatLocale12Hour1Digit(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('h', '١٢', null, 'ar-eg')->getHours()
        );
    }

    public function testCreateFromFormatLocale12Hour1DigitPadding(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('h', '١', null, 'ar-eg')->getHours()
        );
    }

    public function testCreateFromFormatLocale12Hour2Digits(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('hh', '١٢', null, 'ar-eg')->getHours()
        );
    }

    public function testCreateFromFormatLocale12Hour2DigitsPadding(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('hh', '٠١', null, 'ar-eg')->getHours()
        );
    }

    public function testCreateFromFormatLocale23Hour1Digit(): void
    {
        $this->assertSame(
            23,
            DateTime::createFromFormat('H', '٢٣', null, 'ar-eg')->getHours()
        );
    }

    public function testCreateFromFormatLocale23Hour1DigitPadding(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('H', '٠', null, 'ar-eg')->getHours()
        );
    }

    public function testCreateFromFormatLocale23Hour2Digits(): void
    {
        $this->assertSame(
            23,
            DateTime::createFromFormat('HH', '٢٣', null, 'ar-eg')->getHours()
        );
    }

    public function testCreateFromFormatLocale23Hour2DigitsPadding(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('HH', '٠٠', null, 'ar-eg')->getHours()
        );
    }

    public function testCreateFromFormatLocale24Hour1Digit(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('k', '٢٤', null, 'ar-eg')->getHours()
        );
    }

    public function testCreateFromFormatLocale24Hour1DigitPadding(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('k', '١', null, 'ar-eg')->getHours()
        );
    }

    public function testCreateFromFormatLocale24Hour2Digits(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('kk', '٢٤', null, 'ar-eg')->getHours()
        );
    }

    public function testCreateFromFormatLocale24Hour2DigitsPadding(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('kk', '٠١', null, 'ar-eg')->getHours()
        );
    }

    public function testCreateFromFormatLocaleAltWeekDayLong(): void
    {
        $this->assertSame(
            5,
            DateTime::createFromFormat('EEEE', 'пятница', null, 'ru')->getWeekDay()
        );
    }

    public function testCreateFromFormatLocaleAltWeekDayShort(): void
    {
        $this->assertSame(
            5,
            DateTime::createFromFormat('EEE', 'пт', null, 'ru')->getWeekDay()
        );
    }

    public function testCreateFromFormatLocaleDayOfMonth1Digit(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('d', '١', null, 'ar-eg')->getDate()
        );
    }

    public function testCreateFromFormatLocaleDayOfMonth1DigitFull(): void
    {
        $this->assertSame(
            21,
            DateTime::createFromFormat('d', '٢١', null, 'ar-eg')->getDate()
        );
    }

    public function testCreateFromFormatLocaleDayOfMonth2Digits(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('dd', '٠١', null, 'ar-eg')->getDate()
        );
    }

    public function testCreateFromFormatLocaleDayOfMonth2DigitsFull(): void
    {
        $this->assertSame(
            21,
            DateTime::createFromFormat('dd', '٢١', null, 'ar-eg')->getDate()
        );
    }

    public function testCreateFromFormatLocaleDayOfWeekInMonthMonth(): void
    {
        $this->assertSame(
            3,
            DateTime::createFromFormat('F', '٣', null, 'ar-eg')->getWeekDayInMonth()
        );
    }

    public function testCreateFromFormatLocaleDayOfYear1Digit(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('D', '١', null, 'ar-eg')->getDayOfYear()
        );
    }

    public function testCreateFromFormatLocaleDayOfYear1DigitFull(): void
    {
        $this->assertSame(
            152,
            DateTime::createFromFormat('D', '١٥٢', null, 'ar-eg')->getDayOfYear()
        );
    }

    public function testCreateFromFormatLocaleDayOfYear2Digits(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('DD', '٠١', null, 'ar-eg')->getDayOfYear()
        );
    }

    public function testCreateFromFormatLocaleDayOfYear2DigitsFull(): void
    {
        $this->assertSame(
            152,
            DateTime::createFromFormat('DD', '١٥٢', null, 'ar-eg')->getDayOfYear()
        );
    }

    public function testCreateFromFormatLocaleDayOfYear3Digits(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('DDD', '٠٠١', null, 'ar-eg')->getDayOfYear()
        );
    }

    public function testCreateFromFormatLocaleDayOfYear3DigitsFull(): void
    {
        $this->assertSame(
            152,
            DateTime::createFromFormat('DDD', '١٥٢', null, 'ar-eg')->getDayOfYear()
        );
    }

    public function testCreateFromFormatLocaleDayPeriodLong(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('aaaa', '上午', null, 'zh')->getHours()
        );
    }

    public function testCreateFromFormatLocaleDayPeriodLongPm(): void
    {
        $this->assertSame(
            12,
            DateTime::createFromFormat('aaaa', '下午', null, 'zh')->getHours()
        );
    }

    // public function testCreateFromFormatLocaleStandaloneWeekDayNarrow(): void
    // {
    //     $this->assertSame(
    //         1,
    //         DateTime::createFromFormat('ccccc', 'П', null, 'ru')->getWeekDay()
    //     );
    // }

    public function testCreateFromFormatLocaleDayPeriodShort(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('aaa', '上午', null, 'zh')->getHours()
        );
    }

    public function testCreateFromFormatLocaleDayPeriodShortPm(): void
    {
        $this->assertSame(
            12,
            DateTime::createFromFormat('aaa', '下午', null, 'zh')->getHours()
        );
    }

    public function testCreateFromFormatLocaleEraLong(): void
    {
        $this->assertSame(
            1970,
            DateTime::createFromFormat('yyyy GGGG', '1970 от Рождества Христова', null, 'ru')->getYear()
        );
    }

    public function testCreateFromFormatLocaleEraLongBc(): void
    {
        $this->assertSame(
            -1970,
            DateTime::createFromFormat('yyyy GGGG', '1970 до Рождества Христова', null, 'ru')->getYear()
        );
    }

    public function testCreateFromFormatLocaleEraNarrow(): void
    {
        $this->assertSame(
            1970,
            DateTime::createFromFormat('yyyy GGGGG', '1970 н.э.', null, 'ru')->getYear()
        );
    }

    public function testCreateFromFormatLocaleEraNarrowBc(): void
    {
        $this->assertSame(
            -1970,
            DateTime::createFromFormat('yyyy GGGGG', '1970 до н.э.', null, 'ru')->getYear()
        );
    }

    public function testCreateFromFormatLocaleEraShort(): void
    {
        $this->assertSame(
            1970,
            DateTime::createFromFormat('yyyy GGG', '1970 н. э.', null, 'ru')->getYear()
        );
    }

    public function testCreateFromFormatLocaleEraShortBc(): void
    {
        $this->assertSame(
            -1970,
            DateTime::createFromFormat('yyyy GGG', '1970 до н. э.', null, 'ru')->getYear()
        );
    }

    public function testCreateFromFormatLocaleFractional(): void
    {
        $this->assertSame(
            0,
            DateTime::createFromFormat('SSS', '١٢٣', null, 'ar-eg')->getMilliseconds()
        );
    }

    public function testCreateFromFormatLocaleMinute1Digit(): void
    {
        $this->assertSame(
            25,
            DateTime::createFromFormat('m', '٢٥', null, 'ar-eg')->getMinutes()
        );
    }

    public function testCreateFromFormatLocaleMinute1DigitPadding(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('m', '١', null, 'ar-eg')->getMinutes()
        );
    }

    public function testCreateFromFormatLocaleMinute2Digits(): void
    {
        $this->assertSame(
            25,
            DateTime::createFromFormat('mm', '٢٥', null, 'ar-eg')->getMinutes()
        );
    }

    public function testCreateFromFormatLocaleMinute2DigitsPadding(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('mm', '٠١', null, 'ar-eg')->getMinutes()
        );
    }

    public function testCreateFromFormatLocaleMonth1Digit(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('M', '١', null, 'ar-eg')->getMonth()
        );
    }

    public function testCreateFromFormatLocaleMonth1DigitFull(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromFormat('M', '١٠', null, 'ar-eg')->getMonth()
        );
    }

    public function testCreateFromFormatLocaleMonth2Digits(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('MM', '٠١', null, 'ar-eg')->getMonth()
        );
    }

    public function testCreateFromFormatLocaleMonth2DigitsFull(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromFormat('MM', '١٠', null, 'ar-eg')->getMonth()
        );
    }

    public function testCreateFromFormatLocaleMonthLong(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromFormat('MMMM', 'октября', null, 'ru')->getMonth()
        );
    }

    public function testCreateFromFormatLocaleMonthShort(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromFormat('MMM', 'окт.', null, 'ru')->getMonth()
        );
    }

    public function testCreateFromFormatLocaleQuarter1Digit(): void
    {
        $this->assertSame(
            3,
            DateTime::createFromFormat('q', '٣', null, 'ar-eg')->getQuarter()
        );
    }

    public function testCreateFromFormatLocaleQuarter2Digits(): void
    {
        $this->assertSame(
            3,
            DateTime::createFromFormat('qq', '٠٣', null, 'ar-eg')->getQuarter()
        );
    }

    public function testCreateFromFormatLocaleSecond1Digit(): void
    {
        $this->assertSame(
            25,
            DateTime::createFromFormat('s', '٢٥', null, 'ar-eg')->getSeconds()
        );
    }

    public function testCreateFromFormatLocaleSecond1DigitPadding(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('s', '١', null, 'ar-eg')->getSeconds()
        );
    }

    public function testCreateFromFormatLocaleSecond2Digits(): void
    {
        $this->assertSame(
            25,
            DateTime::createFromFormat('ss', '٢٥', null, 'ar-eg')->getSeconds()
        );
    }

    public function testCreateFromFormatLocaleSecond2DigitsPadding(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('ss', '٠١', null, 'ar-eg')->getSeconds()
        );
    }

    public function testCreateFromFormatLocaleStandaloneMonth1Digit(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('L', '١', null, 'ar-eg')->getMonth()
        );
    }

    // public function testCreateFromFormatLocaleMonthNarrow(): void
    // {
    //     $this->assertSame(
    //         10,
    //         DateTime::createFromFormat('MMMMM', 'О', null, 'ru')->getMonth()
    //     );
    // }

    public function testCreateFromFormatLocaleStandaloneMonth1DigitFull(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromFormat('L', '١٠', null, 'ar-eg')->getMonth()
        );
    }

    public function testCreateFromFormatLocaleStandaloneMonth2Digits(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('LL', '٠١', null, 'ar-eg')->getMonth()
        );
    }

    public function testCreateFromFormatLocaleStandaloneMonth2DigitsFull(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromFormat('LL', '١٠', null, 'ar-eg')->getMonth()
        );
    }

    public function testCreateFromFormatLocaleStandaloneMonthLong(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromFormat('LLLL', 'октябрь', null, 'ru')->getMonth()
        );
    }

    public function testCreateFromFormatLocaleStandaloneMonthShort(): void
    {
        $this->assertSame(
            10,
            DateTime::createFromFormat('LLL', 'окт.', null, 'ru')->getMonth()
        );
    }

    public function testCreateFromFormatLocaleStandaloneQuarter1Digit(): void
    {
        $this->assertSame(
            3,
            DateTime::createFromFormat('Q', '٣', null, 'ar-eg')->getQuarter()
        );
    }

    public function testCreateFromFormatLocaleStandaloneQuarter2Digits(): void
    {
        $this->assertSame(
            3,
            DateTime::createFromFormat('QQ', '٠٣', null, 'ar-eg')->getQuarter()
        );
    }

    // public function testCreateFromFormatLocaleWeekDayNarrow(): void
    // {
    //     $this->assertSame(
    //         1,
    //         DateTime::createFromFormat('eeeee', 'П', null, 'ru')->getWeekDay()
    //     );
    // }

    public function testCreateFromFormatLocaleStandaloneWeekDay1Digit(): void
    {
        $this->assertSame(
            5,
            DateTime::createFromFormat('c', '٥', null, 'ar-eg')->getWeekDay()
        );
    }

    public function testCreateFromFormatLocaleStandaloneWeekDay2Digits(): void
    {
        $this->assertSame(
            5,
            DateTime::createFromFormat('cc', '٠٥', null, 'ar-eg')->getWeekDay()
        );
    }

    public function testCreateFromFormatLocaleStandaloneWeekDayLong(): void
    {
        $this->assertSame(
            5,
            DateTime::createFromFormat('cccc', 'пятница', null, 'ru')->getWeekDay()
        );
    }

    public function testCreateFromFormatLocaleStandaloneWeekDayShort(): void
    {
        $this->assertSame(
            5,
            DateTime::createFromFormat('ccc', 'пт', null, 'ru')->getWeekDay()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneIso8601Basic(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss xx', '01/01/2019 00:00:00 +0000', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneIso8601BasicAlt(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss ZZZ', '01/01/2019 00:00:00 +0000', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneIso8601BasicAltTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss ZZZ', '01/01/2019 00:00:00 +1000', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneIso8601BasicShort(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss x', '01/01/2019 00:00:00 +00', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneIso8601BasicShortTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss x', '01/01/2019 00:00:00 +10', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneIso8601BasicShortZ(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss X', '01/01/2019 00:00:00 Z', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneIso8601BasicShortZTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss X', '01/01/2019 00:00:00 +10', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneIso8601BasicTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss xx', '01/01/2019 00:00:00 +1000', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneIso8601BasicZ(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss XX', '01/01/2019 00:00:00 Z', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneIso8601BasicZTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss XX', '01/01/2019 00:00:00 +1000', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneIso8601Extended(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss xxx', '01/01/2019 00:00:00 +00:00', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneIso8601ExtendedAlt(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss ZZZZZ', '01/01/2019 00:00:00 +00:00', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneIso8601ExtendedAltTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss ZZZZZ', '01/01/2019 00:00:00 +10:00', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneIso8601ExtendedTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss xxx', '01/01/2019 00:00:00 +10:00', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneIso8601ExtendedZ(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss XXX', '01/01/2019 00:00:00 Z', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneIso8601ExtendedZTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss XXX', '01/01/2019 00:00:00 +10:00', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneLongBasic(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss ZZZZ', '01/01/2019 00:00:00 GMT+00:00', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneLongBasicTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss ZZZZ', '01/01/2019 00:00:00 GMT+10:00', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneLongLocalized(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss OOOO', '01/01/2019 00:00:00 GMT+00:00', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneLongLocalizedTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss OOOO', '01/01/2019 00:00:00 GMT+10:00', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneLongTimeZoneId(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss VV', '01/01/2019 00:00:00 UTC', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneLongTimeZoneIdTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss VV', '01/01/2019 00:00:00 Australia/Brisbane', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneShortLocalized(): void
    {
        $this->assertSame(
            '2019-01-01T00:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss O', '01/01/2019 00:00:00 GMT+00', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleTimeZoneShortLocalizedTimeZone(): void
    {
        $this->assertSame(
            '2018-12-31T14:00:00.000+00:00',
            DateTime::createFromFormat('dd/MM/yyyy HH:mm:ss O', '01/01/2019 00:00:00 GMT+10', null, 'ru')->toIsoString()
        );
    }

    public function testCreateFromFormatLocaleWeek1Digit(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('w', '١', null, 'ar-eg')->getWeek()
        );
    }

    // public function testCreateFromFormatLocaleStandaloneMonthNarrow(): void
    // {
    //     $this->assertSame(
    //         10,
    //         DateTime::createFromFormat('LLLLL', 'О', null, 'ru')->getMonth()
    //     );
    // }

    public function testCreateFromFormatLocaleWeek1DigitFull(): void
    {
        $this->assertSame(
            22,
            DateTime::createFromFormat('w', '٢٢', null, 'ar-eg')->getWeek()
        );
    }

    public function testCreateFromFormatLocaleWeek2Digits(): void
    {
        $this->assertSame(
            1,
            DateTime::createFromFormat('ww', '٠١', null, 'ar-eg')->getWeek()
        );
    }

    public function testCreateFromFormatLocaleWeek2DigitsFull(): void
    {
        $this->assertSame(
            22,
            DateTime::createFromFormat('ww', '٢٢', null, 'ar-eg')->getWeek()
        );
    }

    // public function testCreateFromFormatLocaleAltWeekDayNarrow(): void
    // {
    //     $this->assertSame(
    //         1,
    //         DateTime::createFromFormat('EEEEE', 'П', null, 'ru')->getWeekDay()
    //     );
    // }

    public function testCreateFromFormatLocaleWeekDay1Digit(): void
    {
        $this->assertSame(
            5,
            DateTime::createFromFormat('e', '٥', null, 'ar-eg')->getWeekDay()
        );
    }

    public function testCreateFromFormatLocaleWeekDay2Digits(): void
    {
        $this->assertSame(
            5,
            DateTime::createFromFormat('ee', '٠٥', null, 'ar-eg')->getWeekDay()
        );
    }

    public function testCreateFromFormatLocaleWeekDayLong(): void
    {
        $this->assertSame(
            5,
            DateTime::createFromFormat('eeee', 'пятница', null, 'ru')->getWeekDay()
        );
    }

    public function testCreateFromFormatLocaleWeekDayShort(): void
    {
        $this->assertSame(
            5,
            DateTime::createFromFormat('eee', 'пт', null, 'ru')->getWeekDay()
        );
    }

    public function testCreateFromFormatLocaleWeekOfMonth(): void
    {
        $this->assertSame(
            3,
            DateTime::createFromFormat('W', '٣', null, 'ar-eg')->getWeekOfMonth()
        );
    }

    public function testCreateFromFormatLocaleWeekYear1Digit(): void
    {
        $this->assertSame(
            5,
            DateTime::createFromFormat('Y w e', '٥ ١ ١', null, 'ar-eg')->getWeekYear()
        );
    }

    public function testCreateFromFormatLocaleWeekYear1DigitFull(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromFormat('Y w e', '٢٠١٨ ١ ٣', null, 'ar-eg')->getWeekYear()
        );
    }

    public function testCreateFromFormatLocaleWeekYear2Digits(): void
    {
        $this->assertSame(
            1988,
            DateTime::createFromFormat('YY w e', '٨٨ ١ ٦', null, 'ar-eg')->getWeekYear()
        );
    }

    public function testCreateFromFormatLocaleWeekYear2DigitsFull(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromFormat('YY w e', '٢٠١٨ ١ ٣', null, 'ar-eg')->getWeekYear()
        );
    }

    public function testCreateFromFormatLocaleWeekYear3Digits(): void
    {
        $this->assertSame(
            88,
            DateTime::createFromFormat('YYY w e', '٠٨٨ ١ ٦', null, 'ar-eg')->getWeekYear()
        );
    }

    public function testCreateFromFormatLocaleWeekYear3DigitsFull(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromFormat('YYY w e', '٢٠١٨ ١ ٣', null, 'ar-eg')->getWeekYear()
        );
    }

    public function testCreateFromFormatLocaleWeekYear4Digits(): void
    {
        $this->assertSame(
            88,
            DateTime::createFromFormat('YYYY w e', '٠٠٨٨ ١ ٦', null, 'ar-eg')->getWeekYear()
        );
    }

    public function testCreateFromFormatLocaleWeekYear4DigitsFull(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromFormat('YYYY w e', '٢٠١٨ ١ ٣', null, 'ar-eg')->getWeekYear()
        );
    }

    public function testCreateFromFormatLocaleYear1Digit(): void
    {
        $this->assertSame(
            5,
            DateTime::createFromFormat('y', '٥', null, 'ar-eg')->getYear()
        );
    }

    public function testCreateFromFormatLocaleYear1DigitFull(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromFormat('y', '٢٠١٨', null, 'ar-eg')->getYear()
        );
    }

    public function testCreateFromFormatLocaleYear2Digits(): void
    {
        $this->assertSame(
            1988,
            DateTime::createFromFormat('yy', '٨٨', null, 'ar-eg')->getYear()
        );
    }

    public function testCreateFromFormatLocaleYear2DigitsFull(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromFormat('yy', '٢٠١٨', null, 'ar-eg')->getYear()
        );
    }

    public function testCreateFromFormatLocaleYear3Digits(): void
    {
        $this->assertSame(
            88,
            DateTime::createFromFormat('yyy', '٠٨٨', null, 'ar-eg')->getYear()
        );
    }

    public function testCreateFromFormatLocaleYear3DigitsFull(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromFormat('yyy', '٢٠١٨', null, 'ar-eg')->getYear()
        );
    }

    public function testCreateFromFormatLocaleYear4Digits(): void
    {
        $this->assertSame(
            88,
            DateTime::createFromFormat('yyyy', '٠٠٨٨', null, 'ar-eg')->getYear()
        );
    }

    public function testCreateFromFormatLocaleYear4DigitsFull(): void
    {
        $this->assertSame(
            2018,
            DateTime::createFromFormat('yyyy', '٢٠١٨', null, 'ar-eg')->getYear()
        );
    }
}
