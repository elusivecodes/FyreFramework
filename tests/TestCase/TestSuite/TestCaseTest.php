<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use Fyre\Core\ErrorHandler;
use Fyre\Core\Loader;
use Fyre\TestSuite\TestCase;
use Override;
use PHPUnit\Framework\SkippedWithMessageException;
use Tests\Mock\Application;

final class TestCaseTest extends TestCase
{
    public function testSkipIf(): void
    {
        $this->expectException(SkippedWithMessageException::class);

        $this->skipIf(true);
    }

    public function testSkipIfFalse(): void
    {
        $this->skipIf(false);
        $this->assertTrue(true);
    }

    public function testSkipUnless(): void
    {
        $this->skipUnless(true);
        $this->assertTrue(true);
    }

    public function testSkipUnlessFalse(): void
    {
        $this->expectException(SkippedWithMessageException::class);

        $this->skipUnless(false);
    }

    #[Override]
    public static function setUpBeforeClass(): void
    {
        $loader = new Loader();
        $app = new Application($loader);

        Application::setInstance($app);
    }

    #[Override]
    public static function tearDownAfterClass(): void
    {
        Application::getInstance()
            ->use(ErrorHandler::class)
            ->unregister();
    }
}
