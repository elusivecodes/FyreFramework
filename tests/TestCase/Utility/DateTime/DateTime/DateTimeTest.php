<?php
declare(strict_types=1);

namespace Tests\TestCase\Utility\DateTime\DateTime;

use Fyre\Core\Traits\MacroTrait;
use Fyre\Core\Traits\StaticMacroTrait;
use Fyre\Utility\DateTime\DateTime;
use Override;
use PHPUnit\Framework\TestCase;

use function array_diff;
use function class_uses;
use function json_encode;
use function serialize;
use function unserialize;

final class DateTimeTest extends TestCase
{
    use AttributesGetTestTrait;
    use AttributesSetTestTrait;
    use ComparisonsTestTrait;
    use CreateTestTrait;
    use DiffTestTrait;
    use FormatLocaleTestTrait;
    use FormatTestTrait;
    use FromFormatLocaleTestTrait;
    use FromFormatTestTrait;
    use ManipulateTestTrait;
    use OutputTestTrait;
    use TransitionTestTrait;
    use UtilityTestTrait;
    use VarsTestTrait;

    public function testDebug(): void
    {
        $this->assertSame(
            [
                'time' => '2019-01-01T00:00:00.000+00:00',
                'timeZone' => 'UTC',
                'locale' => 'en',
            ],
            new DateTime('January 1, 2019')->__debugInfo()
        );
    }

    public function testJsonSerialize(): void
    {
        $date = new DateTime('January 1, 2019');

        $this->assertSame(
            '"2019-01-01T00:00:00.000+00:00"',
            json_encode($date)
        );
    }

    public function testMacro(): void
    {
        $this->assertEmpty(
            array_diff([MacroTrait::class, StaticMacroTrait::class], class_uses(DateTime::class))
        );
    }

    public function testSerializable(): void
    {
        $date = new DateTime('January 1, 2019');

        $this->assertSame(
            $date->toIsoString(),
            unserialize(serialize($date))->toIsoString()
        );
    }

    #[Override]
    protected function setUp(): void
    {
        DateTime::setDefaultLocale('en');
        DateTime::setDefaultTimeZone('UTC');
    }
}
