<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility;

use Fyre\Core\Config;
use Fyre\Core\Container;
use Fyre\Core\Traits\DebugTrait;
use Fyre\Core\Traits\MacroTrait;
use Fyre\DB\TypeParser;
use Fyre\Utility\DateTime\DateTime;
use Fyre\Utility\Formatter;
use Override;
use PHPUnit\Framework\TestCase;

use function class_uses;

final class FormatterTest extends TestCase
{
    protected Formatter $formatter;

    public function testCurrency(): void
    {
        $this->assertSame(
            '$123.00',
            $this->formatter->currency(123)
        );
    }

    public function testCurrencyFloat(): void
    {
        $this->assertSame(
            '$123.46',
            $this->formatter->currency(123.456)
        );
    }

    public function testCurrencyOptions(): void
    {
        $this->assertSame(
            '£123.00',
            $this->formatter->currency(123, 'gbp', 'en-GB')
        );
    }

    public function testCurrencyString(): void
    {
        $this->assertSame(
            '$123.46',
            $this->formatter->currency('123.456')
        );
    }

    public function testDate(): void
    {
        $date = new DateTime('2022-01-01');

        $this->assertSame(
            '01/01/2022',
            $this->formatter->date($date)
        );
    }

    public function testDateOptions(): void
    {
        $date = new DateTime('2022-01-01');

        $this->assertSame(
            '٢٠٢٢-٠١-٠١',
            $this->formatter->date($date, 'yyyy-MM-dd', locale: 'ar-AR')
        );
    }

    public function testDateTime(): void
    {
        $date = new DateTime('2022-01-01 11:59:59');

        $this->assertSame(
            '01/01/2022, 11:59 AM',
            $this->formatter->datetime($date)
        );
    }

    public function testDateTimeOptions(): void
    {
        $date = new DateTime('2022-01-01 11:59:59');

        $this->assertSame(
            '٢٠٢٢-٠١-٠١ ٠٦:٥٩:٥٩',
            $this->formatter->datetime($date, 'yyyy-MM-dd HH:mm:ss', 'America/New_York', 'ar-AR')
        );
    }

    public function testDebug(): void
    {
        $this->assertContains(
            DebugTrait::class,
            class_uses(Formatter::class)
        );
    }

    public function testList(): void
    {
        $data = ['Test 1', 'Test 2', 'Test 3'];

        $this->assertSame(
            'Test 1, Test 2, and Test 3',
            $this->formatter->list($data)
        );
    }

    public function testListOptions(): void
    {
        $data = ['Test 1', 'Test 2', 'Test 3'];

        $this->assertSame(
            'Test 1, Test 2 или Test 3',
            $this->formatter->list($data, 'or', locale: 'ru-RU')
        );
    }

    public function testMacro(): void
    {
        $this->assertContains(
            MacroTrait::class,
            class_uses(Formatter::class)
        );
    }

    public function testNumber(): void
    {
        $this->assertSame(
            '1,234',
            $this->formatter->number(1234)
        );
    }

    public function testNumberFloat(): void
    {
        $this->assertSame(
            '1,234.567',
            $this->formatter->number(1234.567)
        );
    }

    public function testNumberString(): void
    {
        $this->assertSame(
            '1,234.567',
            $this->formatter->number('1234.567')
        );
    }

    public function testPercent(): void
    {
        $this->assertSame(
            '100%',
            $this->formatter->percent(1)
        );
    }

    public function testPercentFloat(): void
    {
        $this->assertSame(
            '12%',
            $this->formatter->percent(0.123)
        );
    }

    public function testPercentString(): void
    {
        $this->assertSame(
            '12%',
            $this->formatter->percent('0.123')
        );
    }

    public function testTime(): void
    {
        $date = new DateTime('2022-01-01 11:59:59');

        $this->assertSame(
            '11:59 AM',
            $this->formatter->time($date)
        );
    }

    public function testTimeOptions(): void
    {
        $date = new DateTime('2022-01-01 11:59:59');

        $this->assertSame(
            '٠٦:٥٩:٥٩',
            $this->formatter->time($date, 'HH:mm:ss', 'America/New_York', 'ar-AR')
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $container = new Container();
        $container->singleton(TypeParser::class);
        $container->singleton(Config::class);
        $container->use(Config::class)->set('App', [
            'defaultLocale' => 'en-US',
            'defaultCurrency' => 'USD',
        ]);

        $this->formatter = $container->build(Formatter::class);

        DateTime::setDefaultLocale('en');
        DateTime::setDefaultTimeZone('UTC');
    }
}
