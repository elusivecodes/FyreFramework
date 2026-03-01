<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Utility\DateTime\DateTime;

trait FormatTestTrait
{
    public function testFormat11Hour1Digit(): void
    {
        $this->assertSame(
            '11',
            DateTime::createFromArray([2018, 1, 1, 23])->format('K')
        );
    }

    public function testFormat11Hour1DigitPadding(): void
    {
        $this->assertSame(
            '0',
            DateTime::createFromArray([2018, 1, 1, 0])->format('K')
        );
    }

    public function testFormat11Hour2Digits(): void
    {
        $this->assertSame(
            '11',
            DateTime::createFromArray([2018, 1, 1, 23])->format('KK')
        );
    }

    public function testFormat11Hour2DigitsPadding(): void
    {
        $this->assertSame(
            '00',
            DateTime::createFromArray([2018, 1, 1, 0])->format('KK')
        );
    }

    public function testFormat12Hour1Digit(): void
    {
        $this->assertSame(
            '12',
            DateTime::createFromArray([2018, 1, 1, 12])->format('h')
        );
    }

    public function testFormat12Hour1DigitPadding(): void
    {
        $this->assertSame(
            '1',
            DateTime::createFromArray([2018, 1, 1, 1])->format('h')
        );
    }

    public function testFormat12Hour2Digits(): void
    {
        $this->assertSame(
            '11',
            DateTime::createFromArray([2018, 1, 1, 23])->format('hh')
        );
    }

    public function testFormat12Hour2DigitsPadding(): void
    {
        $this->assertSame(
            '01',
            DateTime::createFromArray([2018, 1, 1, 1])->format('hh')
        );
    }

    public function testFormat23Hour1Digit(): void
    {
        $this->assertSame(
            '23',
            DateTime::createFromArray([2018, 1, 1, 23])->format('H')
        );
    }

    public function testFormat23Hour1DigitPadding(): void
    {
        $this->assertSame(
            '0',
            DateTime::createFromArray([2018, 1, 1, 0])->format('H')
        );
    }

    public function testFormat23Hour2Digits(): void
    {
        $this->assertSame(
            '23',
            DateTime::createFromArray([2018, 1, 1, 23])->format('HH')
        );
    }

    public function testFormat23Hour2DigitsPadding(): void
    {
        $this->assertSame(
            '00',
            DateTime::createFromArray([2018, 1, 1, 0])->format('HH')
        );
    }

    public function testFormat24Hour1Digit(): void
    {
        $this->assertSame(
            '24',
            DateTime::createFromArray([2018, 1, 1, 0])->format('k')
        );
    }

    public function testFormat24Hour1DigitPadding(): void
    {
        $this->assertSame(
            '1',
            DateTime::createFromArray([2018, 1, 1, 1])->format('k')
        );
    }

    public function testFormat24Hour2Digits(): void
    {
        $this->assertSame(
            '24',
            DateTime::createFromArray([2018, 1, 1, 0])->format('kk')
        );
    }

    public function testFormat24Hour2DigitsPadding(): void
    {
        $this->assertSame(
            '01',
            DateTime::createFromArray([2018, 1, 1, 1])->format('kk')
        );
    }

    public function testFormatAltWeekDayLong(): void
    {
        $this->assertSame(
            'Friday',
            DateTime::createFromArray([2018, 6, 1])->format('EEEE')
        );
    }

    public function testFormatAltWeekDayNarrow(): void
    {
        $this->assertSame(
            'F',
            DateTime::createFromArray([2018, 6, 1])->format('EEEEE')
        );
    }

    public function testFormatAltWeekDayShort(): void
    {
        $this->assertSame(
            'Fri',
            DateTime::createFromArray([2018, 6, 1])->format('EEE')
        );
    }

    public function testFormatDayOfMonth1Digit(): void
    {
        $this->assertSame(
            '21',
            DateTime::createFromArray([2018, 1, 21])->format('d')
        );
    }

    public function testFormatDayOfMonth1DigitPadding(): void
    {
        $this->assertSame(
            '1',
            DateTime::createFromArray([2018, 1, 1])->format('d')
        );
    }

    public function testFormatDayOfMonth2Digits(): void
    {
        $this->assertSame(
            '21',
            DateTime::createFromArray([2018, 1, 21])->format('dd')
        );
    }

    public function testFormatDayOfMonth2DigitsPadding(): void
    {
        $this->assertSame(
            '01',
            DateTime::createFromArray([2018, 1, 1])->format('dd')
        );
    }

    public function testFormatDayOfWeekInMonth(): void
    {
        $this->assertSame(
            '1',
            DateTime::createFromArray([2018, 6, 1])->format('F')
        );
    }

    public function testFormatDayOfWeekInMonthCurrentWeek(): void
    {
        $this->assertSame(
            '1',
            DateTime::createFromArray([2018, 6, 7])->format('F')
        );
    }

    public function testFormatDayOfYear1Digit(): void
    {
        $this->assertSame(
            '152',
            DateTime::createFromArray([2018, 6, 1])->format('D')
        );
    }

    public function testFormatDayOfYear1DigitPadding(): void
    {
        $this->assertSame(
            '1',
            DateTime::createFromArray([2018, 1, 1])->format('D')
        );
    }

    public function testFormatDayOfYear2Digits(): void
    {
        $this->assertSame(
            '152',
            DateTime::createFromArray([2018, 6, 1])->format('DD')
        );
    }

    public function testFormatDayOfYear2DigitsPadding(): void
    {
        $this->assertSame(
            '01',
            DateTime::createFromArray([2018, 1, 1])->format('DD')
        );
    }

    public function testFormatDayOfYear3Digits(): void
    {
        $this->assertSame(
            '152',
            DateTime::createFromArray([2018, 6, 1])->format('DDD')
        );
    }

    public function testFormatDayOfYear3DigitsPadding(): void
    {
        $this->assertSame(
            '001',
            DateTime::createFromArray([2018, 1, 1])->format('DDD')
        );
    }

    public function testFormatDayPeriodLongAm(): void
    {
        $this->assertSame(
            'AM',
            DateTime::createFromArray([2018, 1, 1, 0])->format('aaaa')
        );
    }

    public function testFormatDayPeriodLongPm(): void
    {
        $this->assertSame(
            'PM',
            DateTime::createFromArray([2018, 1, 1, 12])->format('aaaa')
        );
    }

    public function testFormatDayPeriodShortAm(): void
    {
        $this->assertSame(
            'AM',
            DateTime::createFromArray([2018, 1, 1, 0])->format('aaa')
        );
    }

    public function testFormatDayPeriodShortPm(): void
    {
        $this->assertSame(
            'PM',
            DateTime::createFromArray([2018, 1, 1, 12])->format('aaa')
        );
    }

    public function testFormatEraLong(): void
    {
        $this->assertSame(
            'Anno Domini',
            DateTime::createFromArray([2018])->format('GGGG')
        );
    }

    public function testFormatEraLongBc(): void
    {
        $this->assertSame(
            'Before Christ',
            DateTime::createFromArray([-5])->format('GGGG')
        );
    }

    public function testFormatEraNarrow(): void
    {
        $this->assertSame(
            'A',
            DateTime::createFromArray([2018])->format('GGGGG')
        );
    }

    public function testFormatEraNarrowBc(): void
    {
        $this->assertSame(
            'B',
            DateTime::createFromArray([-5])->format('GGGGG')
        );
    }

    public function testFormatEraShort(): void
    {
        $this->assertSame(
            'AD',
            DateTime::createFromArray([2018])->format('GGG')
        );
    }

    public function testFormatEraShortBc(): void
    {
        $this->assertSame(
            'BC',
            DateTime::createFromArray([-5])->format('GGG')
        );
    }

    public function testFormatFractional(): void
    {
        $this->assertSame(
            '123',
            DateTime::createFromArray([2018, 1, 1, 0, 0, 0, 123])->format('SSS')
        );
    }

    public function testFormatFractionalPadding(): void
    {
        $this->assertSame(
            '123000',
            DateTime::createFromArray([2018, 1, 1, 0, 0, 0, 123])->format('SSSSSS')
        );
    }

    public function testFormatFractionalTruncate(): void
    {
        $this->assertSame(
            '1',
            DateTime::createFromArray([2018, 1, 1, 0, 0, 0, 123])->format('S')
        );
    }

    public function testFormatMinute1Digit(): void
    {
        $this->assertSame(
            '25',
            DateTime::createFromArray([2018, 1, 1, 0, 25])->format('m')
        );
    }

    public function testFormatMinute1DigitPadding(): void
    {
        $this->assertSame(
            '1',
            DateTime::createFromArray([2018, 1, 1, 0, 1])->format('m')
        );
    }

    public function testFormatMinute2Digits(): void
    {
        $this->assertSame(
            '25',
            DateTime::createFromArray([2018, 1, 1, 0, 25])->format('mm')
        );
    }

    public function testFormatMinute2DigitsPadding(): void
    {
        $this->assertSame(
            '01',
            DateTime::createFromArray([2018, 1, 1, 0, 1])->format('mm')
        );
    }

    public function testFormatMonth1Digit(): void
    {
        $this->assertSame(
            '10',
            DateTime::createFromArray([2018, 10])->format('M')
        );
    }

    public function testFormatMonth1DigitPadding(): void
    {
        $this->assertSame(
            '1',
            DateTime::createFromArray([2018, 1])->format('M')
        );
    }

    public function testFormatMonth2Digits(): void
    {
        $this->assertSame(
            '10',
            DateTime::createFromArray([2018, 10])->format('MM')
        );
    }

    public function testFormatMonth2DigitsPadding(): void
    {
        $this->assertSame(
            '01',
            DateTime::createFromArray([2018, 1])->format('MM')
        );
    }

    public function testFormatMonthLong(): void
    {
        $this->assertSame(
            'October',
            DateTime::createFromArray([2018, 10])->format('MMMM')
        );
    }

    public function testFormatMonthNarrow(): void
    {
        $this->assertSame(
            'O',
            DateTime::createFromArray([2018, 10])->format('MMMMM')
        );
    }

    public function testFormatMonthShort(): void
    {
        $this->assertSame(
            'Oct',
            DateTime::createFromArray([2018, 10])->format('MMM')
        );
    }

    public function testFormatQuarter1Digit(): void
    {
        $this->assertSame(
            '3',
            DateTime::createFromArray([2018, 8])->format('q')
        );
    }

    public function testFormatQuarter2Digits(): void
    {
        $this->assertSame(
            '03',
            DateTime::createFromArray([2018, 8])->format('qq')
        );
    }

    public function testFormatSecond1Digit(): void
    {
        $this->assertSame(
            '25',
            DateTime::createFromArray([2018, 1, 1, 0, 0, 25])->format('s')
        );
    }

    public function testFormatSecond1DigitPadding(): void
    {
        $this->assertSame(
            '1',
            DateTime::createFromArray([2018, 1, 1, 0, 0, 1])->format('s')
        );
    }

    public function testFormatSecond2Digits(): void
    {
        $this->assertSame(
            '25',
            DateTime::createFromArray([2018, 1, 1, 0, 0, 25])->format('ss')
        );
    }

    public function testFormatSecond2DigitsPadding(): void
    {
        $this->assertSame(
            '01',
            DateTime::createFromArray([2018, 1, 1, 0, 0, 1])->format('ss')
        );
    }

    public function testFormatStandaloneMonth1Digit(): void
    {
        $this->assertSame(
            '10',
            DateTime::createFromArray([2018, 10])->format('L')
        );
    }

    public function testFormatStandaloneMonth1DigitPadding(): void
    {
        $this->assertSame(
            '1',
            DateTime::createFromArray([2018, 1])->format('L')
        );
    }

    public function testFormatStandaloneMonth2Digits(): void
    {
        $this->assertSame(
            '10',
            DateTime::createFromArray([2018, 10])->format('LL')
        );
    }

    public function testFormatStandaloneMonth2DigitsPadding(): void
    {
        $this->assertSame(
            '01',
            DateTime::createFromArray([2018, 1])->format('LL')
        );
    }

    public function testFormatStandaloneMonthLong(): void
    {
        $this->assertSame(
            'October',
            DateTime::createFromArray([2018, 10])->format('LLLL')
        );
    }

    public function testFormatStandaloneMonthNarrow(): void
    {
        $this->assertSame(
            'O',
            DateTime::createFromArray([2018, 10])->format('LLLLL')
        );
    }

    public function testFormatStandaloneMonthShort(): void
    {
        $this->assertSame(
            'Oct',
            DateTime::createFromArray([2018, 10])->format('LLL')
        );
    }

    public function testFormatStandaloneQuarter1Digit(): void
    {
        $this->assertSame(
            '3',
            DateTime::createFromArray([2018, 8])->format('Q')
        );
    }

    public function testFormatStandaloneQuarter2Digits(): void
    {
        $this->assertSame(
            '03',
            DateTime::createFromArray([2018, 8])->format('QQ')
        );
    }

    public function testFormatStandaloneWeekDay1Digit(): void
    {
        $this->assertSame(
            '6',
            DateTime::createFromArray([2018, 6, 1])->format('c')
        );
    }

    public function testFormatStandaloneWeekDay2Digits(): void
    {
        $this->assertSame(
            '6',
            DateTime::createFromArray([2018, 6, 1])->format('cc')
        );
    }

    public function testFormatStandaloneWeekDayLong(): void
    {
        $this->assertSame(
            'Friday',
            DateTime::createFromArray([2018, 6, 1])->format('cccc')
        );
    }

    public function testFormatStandaloneWeekDayNarrow(): void
    {
        $this->assertSame(
            'F',
            DateTime::createFromArray([2018, 6, 1])->format('ccccc')
        );
    }

    public function testFormatStandaloneWeekDayShort(): void
    {
        $this->assertSame(
            'Fri',
            DateTime::createFromArray([2018, 6, 1])->format('ccc')
        );
    }

    public function testFormatTimeZoneIso8601Basic(): void
    {
        $this->assertSame(
            '+0000',
            DateTime::now()->format('xx')
        );
    }

    public function testFormatTimeZoneIso8601BasicAlt(): void
    {
        $this->assertSame(
            '+0000',
            DateTime::now()->format('ZZZ')
        );
    }

    public function testFormatTimeZoneIso8601BasicAltTimeZone(): void
    {
        $this->assertSame(
            '+1000',
            DateTime::now('Australia/Brisbane')->format('ZZZ')
        );
    }

    public function testFormatTimeZoneIso8601BasicShort(): void
    {
        $this->assertSame(
            '+00',
            DateTime::now()->format('x')
        );
    }

    public function testFormatTimeZoneIso8601BasicShortTimeZone(): void
    {
        $this->assertSame(
            '+10',
            DateTime::now('Australia/Brisbane')->format('x')
        );
    }

    public function testFormatTimeZoneIso8601BasicShortZ(): void
    {
        $this->assertSame(
            'Z',
            DateTime::now()->format('X')
        );
    }

    public function testFormatTimeZoneIso8601BasicShortZTimeZone(): void
    {
        $this->assertSame(
            '+10',
            DateTime::now('Australia/Brisbane')->format('X')
        );
    }

    public function testFormatTimeZoneIso8601BasicTimeZone(): void
    {
        $this->assertSame(
            '+1000',
            DateTime::now('Australia/Brisbane')->format('xx')
        );
    }

    public function testFormatTimeZoneIso8601BasicZ(): void
    {
        $this->assertSame(
            'Z',
            DateTime::now()->format('XX')
        );
    }

    public function testFormatTimeZoneIso8601BasicZTimeZone(): void
    {
        $this->assertSame(
            '+1000',
            DateTime::now('Australia/Brisbane')->format('XX')
        );
    }

    public function testFormatTimeZoneIso8601Extended(): void
    {
        $this->assertSame(
            '+00:00',
            DateTime::now()->format('xxx')
        );
    }

    public function testFormatTimeZoneIso8601ExtendedAlt(): void
    {
        $this->assertSame(
            'Z',
            DateTime::now()->format('ZZZZZ')
        );
    }

    public function testFormatTimeZoneIso8601ExtendedAltTimeZone(): void
    {
        $this->assertSame(
            '+10:00',
            DateTime::now('Australia/Brisbane')->format('ZZZZZ')
        );
    }

    public function testFormatTimeZoneIso8601ExtendedTimeZone(): void
    {
        $this->assertSame(
            '+10:00',
            DateTime::now('Australia/Brisbane')->format('xxx')
        );
    }

    public function testFormatTimeZoneIso8601ExtendedZ(): void
    {
        $this->assertSame(
            'Z',
            DateTime::now()->format('XXX')
        );
    }

    public function testFormatTimeZoneIso8601ExtendedZTimeZone(): void
    {
        $this->assertSame(
            '+10:00',
            DateTime::now('Australia/Brisbane')->format('XXX')
        );
    }

    public function testFormatTimeZoneLongBasic(): void
    {
        $this->assertSame(
            'GMT',
            DateTime::now()->format('ZZZZ')
        );
    }

    public function testFormatTimeZoneLongBasicTimeZone(): void
    {
        $this->assertSame(
            'GMT+10:00',
            DateTime::now('Australia/Brisbane')->format('ZZZZ')
        );
    }

    public function testFormatTimeZoneLongLocalized(): void
    {
        $this->assertSame(
            'GMT',
            DateTime::now()->format('OOOO')
        );
    }

    public function testFormatTimeZoneLongLocalizedTimeZone(): void
    {
        $this->assertSame(
            'GMT+10:00',
            DateTime::now('Australia/Brisbane')->format('OOOO')
        );
    }

    public function testFormatTimeZoneLongNonLocation(): void
    {
        $this->assertSame(
            'Coordinated Universal Time',
            DateTime::now()->format('zzzz')
        );
    }

    public function testFormatTimeZoneLongNonLocationTimeZone(): void
    {
        $this->assertSame(
            'Australian Eastern Standard Time',
            DateTime::now('Australia/Brisbane')->format('zzzz')
        );
    }

    public function testFormatTimeZoneLongTimeZoneId(): void
    {
        $this->assertSame(
            'UTC',
            DateTime::now()->format('VV')
        );
    }

    public function testFormatTimeZoneLongTimeZoneIdTimeZone(): void
    {
        $this->assertSame(
            'Australia/Brisbane',
            DateTime::now('Australia/Brisbane')->format('VV')
        );
    }

    public function testFormatTimeZoneShortLocalized(): void
    {
        $this->assertSame(
            'GMT',
            DateTime::now()->format('O')
        );
    }

    public function testFormatTimeZoneShortLocalizedTimeZone(): void
    {
        $this->assertSame(
            'GMT+10',
            DateTime::now('Australia/Brisbane')->format('O')
        );
    }

    public function testFormatTimeZoneShortNonLocation(): void
    {
        $this->assertSame(
            'UTC',
            DateTime::now()->format('zzz')
        );
    }

    public function testFormatTimeZoneShortNonLocationTimeZone(): void
    {
        $this->assertSame(
            'GMT+10',
            DateTime::now('Australia/Brisbane')->format('zzz')
        );
    }

    public function testFormatWeekDay1Digit(): void
    {
        $this->assertSame(
            '6',
            DateTime::createFromArray([2018, 6, 1])->format('e')
        );
    }

    public function testFormatWeekDay2Digits(): void
    {
        $this->assertSame(
            '06',
            DateTime::createFromArray([2018, 6, 1])->format('ee')
        );
    }

    public function testFormatWeekDayLong(): void
    {
        $this->assertSame(
            'Friday',
            DateTime::createFromArray([2018, 6, 1])->format('eeee')
        );
    }

    public function testFormatWeekDayNarrow(): void
    {
        $this->assertSame(
            'F',
            DateTime::createFromArray([2018, 6, 1])->format('eeeee')
        );
    }

    public function testFormatWeekDayShort(): void
    {
        $this->assertSame(
            'Fri',
            DateTime::createFromArray([2018, 6, 1])->format('eee')
        );
    }

    public function testFormatWeekOfMonth(): void
    {
        $this->assertSame(
            '1',
            DateTime::createFromArray([2018, 6, 1])->format('W')
        );
    }

    public function testFormatWeekOfMonthCurrentWeek(): void
    {
        $this->assertSame(
            '2',
            DateTime::createFromArray([2018, 6, 3])->format('W')
        );
    }

    public function testFormatWeekOfYear1Digit(): void
    {
        $this->assertSame(
            '22',
            DateTime::createFromArray([2018, 6])->format('w')
        );
    }

    public function testFormatWeekOfYear1DigitPadding(): void
    {
        $this->assertSame(
            '1',
            DateTime::createFromArray([2018, 1])->format('w')
        );
    }

    public function testFormatWeekOfYear2Digits(): void
    {
        $this->assertSame(
            '22',
            DateTime::createFromArray([2018, 6])->format('ww')
        );
    }

    public function testFormatWeekOfYear2DigitsPadding(): void
    {
        $this->assertSame(
            '01',
            DateTime::createFromArray([2018, 1])->format('ww')
        );
    }

    public function testFormatWeekYear1Digit(): void
    {
        $this->assertSame(
            '2018',
            DateTime::createFromArray([2018])->format('Y')
        );
    }

    public function testFormatWeekYear1DigitCurrentWeek(): void
    {
        $this->assertSame(
            '2020',
            DateTime::createFromArray([2019, 12, 30])->format('Y')
        );
    }

    public function testFormatWeekYear1DigitPadding(): void
    {
        $this->assertSame(
            '5',
            DateTime::createFromArray([5])->format('Y')
        );
    }

    public function testFormatWeekYear2Digits(): void
    {
        $this->assertSame(
            '18',
            DateTime::createFromArray([2018])->format('YY')
        );
    }

    public function testFormatWeekYear2DigitsCurrentWeek(): void
    {
        $this->assertSame(
            '20',
            DateTime::createFromArray([2019, 12, 30])->format('YY')
        );
    }

    public function testFormatWeekYear2DigitsPadding(): void
    {
        $this->assertSame(
            '05',
            DateTime::createFromArray([5])->format('YY')
        );
    }

    public function testFormatWeekYear3Digits(): void
    {
        $this->assertSame(
            '2018',
            DateTime::createFromArray([2018])->format('YYY')
        );
    }

    public function testFormatWeekYear3DigitsCurrentWeek(): void
    {
        $this->assertSame(
            '2020',
            DateTime::createFromArray([2019, 12, 30])->format('YYY')
        );
    }

    public function testFormatWeekYear3DigitsPadding(): void
    {
        $this->assertSame(
            '005',
            DateTime::createFromArray([5])->format('YYY')
        );
    }

    public function testFormatWeekYear4Digits(): void
    {
        $this->assertSame(
            '2018',
            DateTime::createFromArray([2018])->format('YYYY')
        );
    }

    public function testFormatWeekYear4DigitsCurrentWeek(): void
    {
        $this->assertSame(
            '2020',
            DateTime::createFromArray([2019, 12, 30])->format('YYYY')
        );
    }

    public function testFormatWeekYear4DigitsPadding(): void
    {
        $this->assertSame(
            '0005',
            DateTime::createFromArray([5])->format('YYYY')
        );
    }

    public function testFormatYear1Digit(): void
    {
        $this->assertSame(
            '2018',
            DateTime::createFromArray([2018])->format('y')
        );
    }

    public function testFormatYear1DigitPadding(): void
    {
        $this->assertSame(
            '5',
            DateTime::createFromArray([5])->format('y')
        );
    }

    public function testFormatYear2Digits(): void
    {
        $this->assertSame(
            '18',
            DateTime::createFromArray([2018])->format('yy')
        );
    }

    public function testFormatYear2DigitsPadding(): void
    {
        $this->assertSame(
            '05',
            DateTime::createFromArray([5])->format('yy')
        );
    }

    public function testFormatYear3Digits(): void
    {
        $this->assertSame(
            '2018',
            DateTime::createFromArray([2018])->format('yyy')
        );
    }

    public function testFormatYear3DigitsPadding(): void
    {
        $this->assertSame(
            '005',
            DateTime::createFromArray([5])->format('yyy')
        );
    }

    public function testFormatYear4Digits(): void
    {
        $this->assertSame(
            '2018',
            DateTime::createFromArray([2018])->format('yyyy')
        );
    }

    public function testFormatYear4DigitsPadding(): void
    {
        $this->assertSame(
            '0005',
            DateTime::createFromArray([5])->format('yyyy')
        );
    }
}
