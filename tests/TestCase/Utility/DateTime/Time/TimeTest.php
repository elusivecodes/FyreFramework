<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Time;

use Fyre\Core\Traits\MacroTrait;
use Fyre\Core\Traits\StaticMacroTrait;
use Fyre\Utility\DateTime\Time;
use Override;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function class_uses;
use function json_encode;
use function serialize;
use function unserialize;

final class TimeTest extends TestCase
{
    use AttributesGetTestTrait;
    use AttributesWithTestTrait;
    use ComparisonsTestTrait;
    use CreateTestTrait;
    use DiffTestTrait;
    use FormatLocaleTestTrait;
    use FormatTestTrait;
    use FromFormatLocaleTestTrait;
    use FromFormatTestTrait;
    use ManipulateTestTrait;
    use OutputTestTrait;
    use UtilityTestTrait;

    public function testDebug(): void
    {
        $this->assertArraysAreIdentical(
            [
                'time' => '12:30:15.123',
                'timeZone' => 'UTC',
                'locale' => 'en',
            ],
            Time::createFromArray([12, 30, 15, 123])->__debugInfo()
        );
    }

    public function testJsonSerialize(): void
    {
        $this->assertSame(
            '"12:30:15.123"',
            json_encode(Time::createFromArray([12, 30, 15, 123]))
        );
    }

    public function testMacro(): void
    {
        $this->assertEmpty(
            array_diff([MacroTrait::class, StaticMacroTrait::class], class_uses(Time::class))
        );
    }

    public function testSerializable(): void
    {
        $time = Time::createFromArray(
            [12, 30, 15, 123],
            locale: 'ar-eg'
        );
        $result = unserialize(serialize($time));

        $this->assertInstanceOf(
            Time::class,
            $result
        );
        $this->assertArraysAreIdentical(
            ['12:30:15.123', 'UTC', 'ar-eg'],
            [$result->toIsoString(), $result->getTimeZone(), $result->getLocale()]
        );
    }

    #[Override]
    protected function setUp(): void
    {
        Time::setDefaultLocale('en');
        Time::setDefaultTimeZone('UTC');
    }
}
