<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;

trait FormatLocaleTestTrait
{
    public function testFormatLocale11Hour1Digit(): void
    {
        $this->assertSame(
            '١١',
            DateTime::createFromArray([2018, 1, 1, 23], null, 'ar-eg')->format('K')
        );
    }

    public function testFormatLocale11Hour1DigitPadding(): void
    {
        $this->assertSame(
            '٠',
            DateTime::createFromArray([2018, 1, 1, 0], null, 'ar-eg')->format('K')
        );
    }

    public function testFormatLocale11Hour2Digits(): void
    {
        $this->assertSame(
            '١١',
            DateTime::createFromArray([2018, 1, 1, 23], null, 'ar-eg')->format('KK')
        );
    }

    public function testFormatLocale11Hour2DigitsPadding(): void
    {
        $this->assertSame(
            '٠٠',
            DateTime::createFromArray([2018, 1, 1, 0], null, 'ar-eg')->format('KK')
        );
    }

    public function testFormatLocale12Hour1Digit(): void
    {
        $this->assertSame(
            '١٢',
            DateTime::createFromArray([2018, 1, 1, 12], null, 'ar-eg')->format('h')
        );
    }

    public function testFormatLocale12Hour1DigitPadding(): void
    {
        $this->assertSame(
            '١',
            DateTime::createFromArray([2018, 1, 1, 1], null, 'ar-eg')->format('h')
        );
    }

    public function testFormatLocale12Hour2Digits(): void
    {
        $this->assertSame(
            '١١',
            DateTime::createFromArray([2018, 1, 1, 23], null, 'ar-eg')->format('hh')
        );
    }

    public function testFormatLocale12Hour2DigitsPadding(): void
    {
        $this->assertSame(
            '٠١',
            DateTime::createFromArray([2018, 1, 1, 1], null, 'ar-eg')->format('hh')
        );
    }

    public function testFormatLocale23Hour1Digit(): void
    {
        $this->assertSame(
            '٢٣',
            DateTime::createFromArray([2018, 1, 1, 23], null, 'ar-eg')->format('H')
        );
    }

    public function testFormatLocale23Hour1DigitPadding(): void
    {
        $this->assertSame(
            '٠',
            DateTime::createFromArray([2018, 1, 1, 0], null, 'ar-eg')->format('H')
        );
    }

    public function testFormatLocale23Hour2Digits(): void
    {
        $this->assertSame(
            '٢٣',
            DateTime::createFromArray([2018, 1, 1, 23], null, 'ar-eg')->format('HH')
        );
    }

    public function testFormatLocale23Hour2DigitsPadding(): void
    {
        $this->assertSame(
            '٠٠',
            DateTime::createFromArray([2018, 1, 1, 0], null, 'ar-eg')->format('HH')
        );
    }

    public function testFormatLocale24Hour1Digit(): void
    {
        $this->assertSame(
            '٢٤',
            DateTime::createFromArray([2018, 1, 1, 0], null, 'ar-eg')->format('k')
        );
    }

    public function testFormatLocale24Hour1DigitPadding(): void
    {
        $this->assertSame(
            '١',
            DateTime::createFromArray([2018, 1, 1, 1], null, 'ar-eg')->format('k')
        );
    }

    public function testFormatLocale24Hour2Digits(): void
    {
        $this->assertSame(
            '٢٤',
            DateTime::createFromArray([2018, 1, 1, 0], null, 'ar-eg')->format('kk')
        );
    }

    public function testFormatLocale24Hour2DigitsPadding(): void
    {
        $this->assertSame(
            '٠١',
            DateTime::createFromArray([2018, 1, 1, 1], null, 'ar-eg')->format('kk')
        );
    }

    public function testFormatLocaleAltWeekDayLong(): void
    {
        $this->assertSame(
            'пятница',
            DateTime::createFromArray([2018, 6, 1], null, 'ru')->format('EEEE')
        );
    }

    public function testFormatLocaleAltWeekDayShort(): void
    {
        $this->assertSame(
            'пт',
            DateTime::createFromArray([2018, 6, 1], null, 'ru')->format('EEE')
        );
    }

    public function testFormatLocaleDayOfMonth1Digit(): void
    {
        $this->assertSame(
            '٢١',
            DateTime::createFromArray([2019, 1, 21], null, 'ar-eg')->format('d')
        );
    }

    public function testFormatLocaleDayOfMonth1DigitPadding(): void
    {
        $this->assertSame(
            '١',
            DateTime::createFromArray([2019, 1, 1], null, 'ar-eg')->format('d')
        );
    }

    public function testFormatLocaleDayOfMonth2Digits(): void
    {
        $this->assertSame(
            '٢١',
            DateTime::createFromArray([2019, 1, 21], null, 'ar-eg')->format('dd')
        );
    }

    public function testFormatLocaleDayOfMonth2DigitsPadding(): void
    {
        $this->assertSame(
            '٠١',
            DateTime::createFromArray([2019, 1, 1], null, 'ar-eg')->format('dd')
        );
    }

    public function testFormatLocaleDayOfWeekInMonth(): void
    {
        $this->assertSame(
            '١',
            DateTime::createFromArray([2019, 1, 1], null, 'ar-eg')->format('F')
        );
    }

    public function testFormatLocaleDayOfWeekInMonthCurrentWeek(): void
    {
        $this->assertSame(
            '١',
            DateTime::createFromArray([2019, 6, 7], null, 'ar-eg')->format('F')
        );
    }

    public function testFormatLocaleDayOfYear1Digit(): void
    {
        $this->assertSame(
            '١٥٢',
            DateTime::createFromArray([2019, 6, 1], null, 'ar-eg')->format('D')
        );
    }

    public function testFormatLocaleDayOfYear1DigitPadding(): void
    {
        $this->assertSame(
            '١',
            DateTime::createFromArray([2019, 1, 1], null, 'ar-eg')->format('D')
        );
    }

    public function testFormatLocaleDayOfYear2Digits(): void
    {
        $this->assertSame(
            '١٥٢',
            DateTime::createFromArray([2019, 6, 1], null, 'ar-eg')->format('DD')
        );
    }

    public function testFormatLocaleDayOfYear2DigitsPadding(): void
    {
        $this->assertSame(
            '٠١',
            DateTime::createFromArray([2019, 1, 1], null, 'ar-eg')->format('DD')
        );
    }

    public function testFormatLocaleDayOfYear3Digits(): void
    {
        $this->assertSame(
            '١٥٢',
            DateTime::createFromArray([2019, 6, 1], null, 'ar-eg')->format('DDD')
        );
    }

    public function testFormatLocaleDayOfYear3DigitsPadding(): void
    {
        $this->assertSame(
            '٠٠١',
            DateTime::createFromArray([2019, 1, 1], null, 'ar-eg')->format('DDD')
        );
    }

    public function testFormatLocaleDayPeriodLongAm(): void
    {
        $this->assertSame(
            '上午',
            DateTime::createFromArray([2018, 1, 1, 0], null, 'zh')->format('aaaa')
        );
    }

    public function testFormatLocaleDayPeriodLongPm(): void
    {
        $this->assertSame(
            '下午',
            DateTime::createFromArray([2018, 1, 1, 12], null, 'zh')->format('aaaa')
        );
    }

    public function testFormatLocaleDayPeriodShortAm(): void
    {
        $this->assertSame(
            '上午',
            DateTime::createFromArray([2018, 1, 1, 0], null, 'zh')->format('aaa')
        );
    }

    public function testFormatLocaleDayPeriodShortPm(): void
    {
        $this->assertSame(
            '下午',
            DateTime::createFromArray([2018, 1, 1, 12], null, 'zh')->format('aaa')
        );
    }

    public function testFormatLocaleEraLong(): void
    {
        $this->assertSame(
            'от Рождества Христова',
            DateTime::createFromArray([2018], null, 'ru')->format('GGGG')
        );
    }

    public function testFormatLocaleEraLongBc(): void
    {
        $this->assertSame(
            'до Рождества Христова',
            DateTime::createFromArray([-5], null, 'ru')->format('GGGG')
        );
    }

    public function testFormatLocaleEraNarrow(): void
    {
        $this->assertSame(
            'н.э.',
            DateTime::createFromArray([2018], null, 'ru')->format('GGGGG')
        );
    }

    public function testFormatLocaleEraNarrowBc(): void
    {
        $this->assertSame(
            'до н.э.',
            DateTime::createFromArray([-5], null, 'ru')->format('GGGGG')
        );
    }

    public function testFormatLocaleEraShort(): void
    {
        $this->assertSame(
            'н. э.',
            DateTime::createFromArray([2018], null, 'ru')->format('GGG')
        );
    }

    public function testFormatLocaleEraShortBc(): void
    {
        $this->assertSame(
            'до н. э.',
            DateTime::createFromArray([-5], null, 'ru')->format('GGG')
        );
    }

    public function testFormatLocaleFractional(): void
    {
        $this->assertSame(
            '١٢٣',
            DateTime::createFromArray([2018, 1, 1, 0, 0, 0, 123], null, 'ar-eg')->format('SSS')
        );
    }

    public function testFormatLocaleFractionalPadding(): void
    {
        $this->assertSame(
            '١٢٣٠٠٠',
            DateTime::createFromArray([2018, 1, 1, 0, 0, 0, 123], null, 'ar-eg')->format('SSSSSS')
        );
    }

    public function testFormatLocaleFractionalTruncate(): void
    {
        $this->assertSame(
            '١',
            DateTime::createFromArray([2018, 1, 1, 0, 0, 0, 123], null, 'ar-eg')->format('S')
        );
    }

    public function testFormatLocaleMinute1Digit(): void
    {
        $this->assertSame(
            '٢٥',
            DateTime::createFromArray([2018, 1, 1, 0, 25], null, 'ar-eg')->format('m')
        );
    }

    public function testFormatLocaleMinute1DigitPadding(): void
    {
        $this->assertSame(
            '١',
            DateTime::createFromArray([2018, 1, 1, 0, 1], null, 'ar-eg')->format('m')
        );
    }

    public function testFormatLocaleMinute2Digits(): void
    {
        $this->assertSame(
            '٢٥',
            DateTime::createFromArray([2018, 1, 1, 0, 25], null, 'ar-eg')->format('mm')
        );
    }

    public function testFormatLocaleMinute2DigitsPadding(): void
    {
        $this->assertSame(
            '٠١',
            DateTime::createFromArray([2018, 1, 1, 0, 1], null, 'ar-eg')->format('mm')
        );
    }

    public function testFormatLocaleMonth1Digit(): void
    {
        $this->assertSame(
            '١٠',
            DateTime::createFromArray([2018, 10], null, 'ar-eg')->format('M')
        );
    }

    public function testFormatLocaleMonth1DigitPadding(): void
    {
        $this->assertSame(
            '١',
            DateTime::createFromArray([2018, 1], null, 'ar-eg')->format('M')
        );
    }

    public function testFormatLocaleMonth2Digits(): void
    {
        $this->assertSame(
            '١٠',
            DateTime::createFromArray([2018, 10], null, 'ar-eg')->format('MM')
        );
    }

    public function testFormatLocaleMonth2DigitsPadding(): void
    {
        $this->assertSame(
            '٠١',
            DateTime::createFromArray([2018, 1], null, 'ar-eg')->format('MM')
        );
    }

    public function testFormatLocaleMonthLong(): void
    {
        $this->assertSame(
            'октября',
            DateTime::createFromArray([2018, 10], null, 'ru')->format('MMMM')
        );
    }

    public function testFormatLocaleMonthNarrow(): void
    {
        $this->assertSame(
            'О',
            DateTime::createFromArray([2018, 10], null, 'ru')->format('MMMMM')
        );
    }

    public function testFormatLocaleMonthShort(): void
    {
        $this->assertSame(
            'окт.',
            DateTime::createFromArray([2018, 10], null, 'ru')->format('MMM')
        );
    }

    public function testFormatLocaleQuarter1Digit(): void
    {
        $this->assertSame(
            '٣',
            DateTime::createFromArray([2018, 8], null, 'ar-eg')->format('q')
        );
    }

    public function testFormatLocaleQuarter2Digits(): void
    {
        $this->assertSame(
            '٠٣',
            DateTime::createFromArray([2018, 8], null, 'ar-eg')->format('qq')
        );
    }

    public function testFormatLocaleSecond1Digit(): void
    {
        $this->assertSame(
            '٢٥',
            DateTime::createFromArray([2018, 1, 1, 0, 0, 25], null, 'ar-eg')->format('s')
        );
    }

    public function testFormatLocaleSecond1DigitPadding(): void
    {
        $this->assertSame(
            '١',
            DateTime::createFromArray([2018, 1, 1, 0, 0, 1], null, 'ar-eg')->format('s')
        );
    }

    public function testFormatLocaleSecond2Digits(): void
    {
        $this->assertSame(
            '٢٥',
            DateTime::createFromArray([2018, 1, 1, 0, 0, 25], null, 'ar-eg')->format('ss')
        );
    }

    public function testFormatLocaleSecond2DigitsPadding(): void
    {
        $this->assertSame(
            '٠١',
            DateTime::createFromArray([2018, 1, 1, 0, 0, 1], null, 'ar-eg')->format('ss')
        );
    }

    public function testFormatLocaleStandaloneMonth1Digit(): void
    {
        $this->assertSame(
            '١٠',
            DateTime::createFromArray([2018, 10], null, 'ar-eg')->format('L')
        );
    }

    public function testFormatLocaleStandaloneMonth1DigitPadding(): void
    {
        $this->assertSame(
            '١',
            DateTime::createFromArray([2018, 1], null, 'ar-eg')->format('L')
        );
    }

    public function testFormatLocaleStandaloneMonth2Digits(): void
    {
        $this->assertSame(
            '١٠',
            DateTime::createFromArray([2018, 10], null, 'ar-eg')->format('LL')
        );
    }

    public function testFormatLocaleStandaloneMonth2DigitsPadding(): void
    {
        $this->assertSame(
            '٠١',
            DateTime::createFromArray([2018, 1], null, 'ar-eg')->format('LL')
        );
    }

    public function testFormatLocaleStandaloneMonthLong(): void
    {
        $this->assertSame(
            'октябрь',
            DateTime::createFromArray([2018, 10], null, 'ru')->format('LLLL')
        );
    }

    public function testFormatLocaleStandaloneMonthNarrow(): void
    {
        $this->assertSame(
            'О',
            DateTime::createFromArray([2018, 10], null, 'ru')->format('LLLLL')
        );
    }

    public function testFormatLocaleStandaloneMonthShort(): void
    {
        $this->assertSame(
            'окт.',
            DateTime::createFromArray([2018, 10], null, 'ru')->format('LLL')
        );
    }

    public function testFormatLocaleStandaloneQuarter1Digit(): void
    {
        $this->assertSame(
            '٣',
            DateTime::createFromArray([2018, 8], null, 'ar-eg')->format('Q')
        );
    }

    public function testFormatLocaleStandaloneQuarter2Digits(): void
    {
        $this->assertSame(
            '٠٣',
            DateTime::createFromArray([2018, 8], null, 'ar-eg')->format('QQ')
        );
    }

    // public function testFormatLocaleWeekDayNarrow(): void
    // {
    //     $this->assertSame(
    //         'П',
    //         DateTime::createFromArray([2018, 6, 1], null, 'ru')->format('eeeee')
    //     );
    // }

    public function testFormatLocaleStandaloneWeekDay1Digit(): void
    {
        $this->assertSame(
            '٧',
            DateTime::createFromArray([2018, 6, 1], null, 'ar-eg')->format('c')
        );
    }

    public function testFormatLocaleStandaloneWeekDay2Digits(): void
    {
        $this->assertSame(
            '٧',
            DateTime::createFromArray([2018, 6, 1], null, 'ar-eg')->format('cc')
        );
    }

    public function testFormatLocaleStandaloneWeekDayLong(): void
    {
        $this->assertSame(
            'пятница',
            DateTime::createFromArray([2018, 6, 1], null, 'ru')->format('cccc')
        );
    }

    public function testFormatLocaleStandaloneWeekDayNarrow(): void
    {
        $this->assertSame(
            'П',
            DateTime::createFromArray([2018, 6, 1], null, 'ru')->format('ccccc')
        );
    }

    public function testFormatLocaleStandaloneWeekDayShort(): void
    {
        $this->assertSame(
            'пт',
            DateTime::createFromArray([2018, 6, 1], null, 'ru')->format('ccc')
        );
    }

    public function testFormatLocaleTimeZoneIso8601Basic(): void
    {
        $this->assertSame(
            '+0000',
            DateTime::now(null, 'ru')->format('xx')
        );
    }

    // public function testFormatLocaleTimeZoneLongNonLocationTimeZone(): void
    // {
    //     $this->assertSame(
    //         'Восточная Австралия, стандартное время',
    //         DateTime::now('Australia/Brisbane', null, 'ru')->format('zzzz')
    //     );
    // }

    public function testFormatLocaleTimeZoneIso8601BasicAlt(): void
    {
        $this->assertSame(
            '+0000',
            DateTime::now(null, 'ru')->format('ZZZ')
        );
    }

    public function testFormatLocaleTimeZoneIso8601BasicAltTimeZone(): void
    {
        $this->assertSame(
            '+1000',
            DateTime::now('Australia/Brisbane', 'ru')->format('ZZZ')
        );
    }

    public function testFormatLocaleTimeZoneIso8601BasicShort(): void
    {
        $this->assertSame(
            '+00',
            DateTime::now(null, 'ru')->format('x')
        );
    }

    public function testFormatLocaleTimeZoneIso8601BasicShortTimeZone(): void
    {
        $this->assertSame(
            '+10',
            DateTime::now('Australia/Brisbane', 'ru')->format('x')
        );
    }

    public function testFormatLocaleTimeZoneIso8601BasicShortZ(): void
    {
        $this->assertSame(
            'Z',
            DateTime::now(null, 'ru')->format('X')
        );
    }

    public function testFormatLocaleTimeZoneIso8601BasicShortZTimeZone(): void
    {
        $this->assertSame(
            '+10',
            DateTime::now('Australia/Brisbane', 'ru')->format('X')
        );
    }

    public function testFormatLocaleTimeZoneIso8601BasicTimeZone(): void
    {
        $this->assertSame(
            '+1000',
            DateTime::now('Australia/Brisbane', 'ru')->format('xx')
        );
    }

    public function testFormatLocaleTimeZoneIso8601BasicZ(): void
    {
        $this->assertSame(
            'Z',
            DateTime::now(null, 'ru')->format('XX')
        );
    }

    public function testFormatLocaleTimeZoneIso8601BasicZTimeZone(): void
    {
        $this->assertSame(
            '+1000',
            DateTime::now('Australia/Brisbane', 'ru')->format('XX')
        );
    }

    public function testFormatLocaleTimeZoneIso8601Extended(): void
    {
        $this->assertSame(
            '+00:00',
            DateTime::now(null, 'ru')->format('xxx')
        );
    }

    public function testFormatLocaleTimeZoneIso8601ExtendedAlt(): void
    {
        $this->assertSame(
            'Z',
            DateTime::now(null, 'ru')->format('ZZZZZ')
        );
    }

    public function testFormatLocaleTimeZoneIso8601ExtendedAltTimeZone(): void
    {
        $this->assertSame(
            '+10:00',
            DateTime::now('Australia/Brisbane', 'ru')->format('ZZZZZ')
        );
    }

    public function testFormatLocaleTimeZoneIso8601ExtendedTimeZone(): void
    {
        $this->assertSame(
            '+10:00',
            DateTime::now('Australia/Brisbane', 'ru')->format('xxx')
        );
    }

    public function testFormatLocaleTimeZoneIso8601ExtendedZ(): void
    {
        $this->assertSame(
            'Z',
            DateTime::now(null, 'ru')->format('XXX')
        );
    }

    public function testFormatLocaleTimeZoneIso8601ExtendedZTimeZone(): void
    {
        $this->assertSame(
            '+10:00',
            DateTime::now('Australia/Brisbane', 'ru')->format('XXX')
        );
    }

    public function testFormatLocaleTimeZoneLongBasic(): void
    {
        $this->assertSame(
            'GMT',
            DateTime::now(null, 'ru')->format('ZZZZ')
        );
    }

    public function testFormatLocaleTimeZoneLongBasicTimeZone(): void
    {
        $this->assertSame(
            'GMT+10:00',
            DateTime::now('Australia/Brisbane', 'ru')->format('ZZZZ')
        );
    }

    public function testFormatLocaleTimeZoneLongLocalized(): void
    {
        $this->assertSame(
            'GMT',
            DateTime::now(null, 'ru')->format('OOOO')
        );
    }

    public function testFormatLocaleTimeZoneLongLocalizedTimeZone(): void
    {
        $this->assertSame(
            'GMT+10:00',
            DateTime::now('Australia/Brisbane', 'ru')->format('OOOO')
        );
    }

    public function testFormatLocaleTimeZoneLongNonLocation(): void
    {
        $this->assertSame(
            'Всемирное координированное время',
            DateTime::now(null, 'ru')->format('zzzz')
        );
    }

    public function testFormatLocaleTimeZoneLongTimeZoneId(): void
    {
        $this->assertSame(
            'UTC',
            DateTime::now(null, 'ru')->format('VV')
        );
    }

    public function testFormatLocaleTimeZoneLongTimeZoneIdTimeZone(): void
    {
        $this->assertSame(
            'Australia/Brisbane',
            DateTime::now('Australia/Brisbane', 'ru')->format('VV')
        );
    }

    public function testFormatLocaleTimeZoneShortLocalized(): void
    {
        $this->assertSame(
            'GMT',
            DateTime::now(null, 'ru')->format('O')
        );
    }

    public function testFormatLocaleTimeZoneShortLocalizedTimeZone(): void
    {
        $this->assertSame(
            'GMT+10',
            DateTime::now('Australia/Brisbane', 'ru')->format('O')
        );
    }

    public function testFormatLocaleTimeZoneShortNonLocation(): void
    {
        $this->assertSame(
            'UTC',
            DateTime::now(null, 'ru')->format('zzz')
        );
    }

    public function testFormatLocaleTimeZoneShortNonLocationTimeZone(): void
    {
        $this->assertSame(
            'GMT+10',
            DateTime::now('Australia/Brisbane', 'ru')->format('zzz')
        );
    }

    // public function testFormatLocaleAltWeekDayNarrow(): void
    // {
    //     $this->assertSame(
    //         'П',
    //         DateTime::createFromArray([2018, 6, 1], null, 'ru')->format('EEEEE')
    //     );
    // }

    public function testFormatLocaleWeekDay1Digit(): void
    {
        $this->assertSame(
            '٧',
            DateTime::createFromArray([2018, 6, 1], null, 'ar-eg')->format('e')
        );
    }

    public function testFormatLocaleWeekDay2Digits(): void
    {
        $this->assertSame(
            '٠٧',
            DateTime::createFromArray([2018, 6, 1], null, 'ar-eg')->format('ee')
        );
    }

    public function testFormatLocaleWeekDayLong(): void
    {
        $this->assertSame(
            'пятница',
            DateTime::createFromArray([2018, 6, 1], null, 'ru')->format('eeee')
        );
    }

    public function testFormatLocaleWeekDayShort(): void
    {
        $this->assertSame(
            'пт',
            DateTime::createFromArray([2018, 6, 1], null, 'ru')->format('eee')
        );
    }

    public function testFormatLocaleWeekOfMonth(): void
    {
        $this->assertSame(
            '١',
            DateTime::createFromArray([2019, 6, 1], null, 'ar-eg')->format('W')
        );
    }

    public function testFormatLocaleWeekOfMonthCurrentWeek(): void
    {
        $this->assertSame(
            '٢',
            DateTime::createFromArray([2019, 6, 8], null, 'ar-eg')->format('W')
        );
    }

    public function testFormatLocaleWeekOfYear1Digit(): void
    {
        $this->assertSame(
            '٢٢',
            DateTime::createFromArray([2018, 6], null, 'ar-eg')->format('w')
        );
    }

    public function testFormatLocaleWeekOfYear1DigitPadding(): void
    {
        $this->assertSame(
            '١',
            DateTime::createFromArray([2018, 1], null, 'ar-eg')->format('w')
        );
    }

    public function testFormatLocaleWeekOfYear2Digits(): void
    {
        $this->assertSame(
            '٢٢',
            DateTime::createFromArray([2018, 6], null, 'ar-eg')->format('ww')
        );
    }

    public function testFormatLocaleWeekOfYear2DigitsPadding(): void
    {
        $this->assertSame(
            '٠١',
            DateTime::createFromArray([2018, 1], null, 'ar-eg')->format('ww')
        );
    }

    public function testFormatLocaleWeekYear1Digit(): void
    {
        $this->assertSame(
            '٢٠١٨',
            DateTime::createFromArray([2018], null, 'ar-eg')->format('Y')
        );
    }

    public function testFormatLocaleWeekYear1DigitCurrentWeek(): void
    {
        $this->assertSame(
            '٢٠٢٠',
            DateTime::createFromArray([2019, 12, 30], null, 'ar-eg')->format('Y')
        );
    }

    public function testFormatLocaleWeekYear1DigitPadding(): void
    {
        $this->assertSame(
            '٥',
            DateTime::createFromArray([5, 2], null, 'ar-eg')->format('Y')
        );
    }

    public function testFormatLocaleWeekYear2Digits(): void
    {
        $this->assertSame(
            '١٨',
            DateTime::createFromArray([2018], null, 'ar-eg')->format('YY')
        );
    }

    public function testFormatLocaleWeekYear2DigitsCurrentWeek(): void
    {
        $this->assertSame(
            '٢٠',
            DateTime::createFromArray([2019, 12, 30], null, 'ar-eg')->format('YY')
        );
    }

    public function testFormatLocaleWeekYear2DigitsPadding(): void
    {
        $this->assertSame(
            '٠٥',
            DateTime::createFromArray([5, 2], null, 'ar-eg')->format('YY')
        );
    }

    public function testFormatLocaleWeekYear3Digits(): void
    {
        $this->assertSame(
            '٢٠١٨',
            DateTime::createFromArray([2018], null, 'ar-eg')->format('YYY')
        );
    }

    public function testFormatLocaleWeekYear3DigitsCurrentWeek(): void
    {
        $this->assertSame(
            '٢٠٢٠',
            DateTime::createFromArray([2019, 12, 30], null, 'ar-eg')->format('YYY')
        );
    }

    public function testFormatLocaleWeekYear3DigitsPadding(): void
    {
        $this->assertSame(
            '٠٠٥',
            DateTime::createFromArray([5], null, 'ar-eg')->format('YYY')
        );
    }

    public function testFormatLocaleWeekYear4Digits(): void
    {
        $this->assertSame(
            '٢٠١٨',
            DateTime::createFromArray([2018], null, 'ar-eg')->format('YYYY')
        );
    }

    public function testFormatLocaleWeekYear4DigitsCurrentWeek(): void
    {
        $this->assertSame(
            '٢٠٢٠',
            DateTime::createFromArray([2019, 12, 30], null, 'ar-eg')->format('YYYY')
        );
    }

    public function testFormatLocaleWeekYear4DigitsPadding(): void
    {
        $this->assertSame(
            '٠٠٠٥',
            DateTime::createFromArray([5], null, 'ar-eg')->format('YYYY')
        );
    }

    public function testFormatLocaleYear1Digit(): void
    {
        $this->assertSame(
            '٢٠١٨',
            DateTime::createFromArray([2018], null, 'ar-eg')->format('y')
        );
    }

    public function testFormatLocaleYear1DigitPadding(): void
    {
        $this->assertSame(
            '٥',
            DateTime::createFromArray([5], null, 'ar-eg')->format('y')
        );
    }

    public function testFormatLocaleYear2Digits(): void
    {
        $this->assertSame(
            '١٨',
            DateTime::createFromArray([2018], null, 'ar-eg')->format('yy')
        );
    }

    public function testFormatLocaleYear2DigitsPadding(): void
    {
        $this->assertSame(
            '٠٥',
            DateTime::createFromArray([5], null, 'ar-eg')->format('yy')
        );
    }

    public function testFormatLocaleYear3Digits(): void
    {
        $this->assertSame(
            '٢٠١٨',
            DateTime::createFromArray([2018], null, 'ar-eg')->format('yyy')
        );
    }

    public function testFormatLocaleYear3DigitsPadding(): void
    {
        $this->assertSame(
            '٠٠٥',
            DateTime::createFromArray([5], null, 'ar-eg')->format('yyy')
        );
    }

    public function testFormatLocaleYear4Digits(): void
    {
        $this->assertSame(
            '٢٠١٨',
            DateTime::createFromArray([2018], null, 'ar-eg')->format('yyyy')
        );
    }

    public function testFormatLocaleYear4DigitsPadding(): void
    {
        $this->assertSame(
            '٠٠٠٥',
            DateTime::createFromArray([5], null, 'ar-eg')->format('yyyy')
        );
    }
}
