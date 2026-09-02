<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\Date;

use Fyre\Core\Traits\MacroTrait;
use Fyre\Core\Traits\StaticMacroTrait;
use Fyre\Utility\DateTime\Date;
use Override;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function class_uses;
use function json_encode;
use function serialize;
use function unserialize;

final class DateTest extends TestCase
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
                'time' => '2019-01-01',
                'timeZone' => 'UTC',
                'locale' => 'en',
            ],
            Date::createFromArray([2019, 1, 1])->__debugInfo()
        );
    }

    public function testJsonSerialize(): void
    {
        $this->assertSame(
            '"2019-01-01"',
            json_encode(Date::createFromArray([2019, 1, 1]))
        );
    }

    public function testMacro(): void
    {
        $this->assertEmpty(
            array_diff([MacroTrait::class, StaticMacroTrait::class], class_uses(Date::class))
        );
    }

    public function testSerializable(): void
    {
        $date = Date::createFromArray(
            [2019, 1, 1],
            locale: 'ar-eg'
        );
        $result = unserialize(serialize($date));

        $this->assertInstanceOf(
            Date::class,
            $result
        );
        $this->assertArraysAreIdentical(
            ['2019-01-01', 'UTC', 'ar-eg'],
            [$result->toIsoString(), $result->getTimeZone(), $result->getLocale()]
        );
    }

    #[Override]
    protected function setUp(): void
    {
        Date::setDefaultLocale('en');
        Date::setDefaultTimeZone('UTC');
        Date::withDateClamping(true);
    }
}
