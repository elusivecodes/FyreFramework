<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Console;

use Fyre\Core\ErrorHandler;
use Fyre\Core\Loader;
use Fyre\TestSuite\TestCase;
use Fyre\TestSuite\Traits\ConsoleTestTrait;
use Override;
use PHPUnit\Framework\Attributes\Before;
use Tests\Mock\Application;

final class ConsoleTest extends TestCase
{
    use ConsoleTestTrait;
    use ContentsContainsRowTrait;
    use ContentsContainsTrait;
    use ContentsEmptyTrait;
    use ContentsNotContainsTrait;
    use ContentsRegExpTrait;
    use ExitCodeTrait;

    public function testExec(): void
    {
        $this->exec('arguments --value value');

        $this->assertExitSuccess();
    }

    public function testExecInput(): void
    {
        $this->exec('arguments', [
            'value',
        ]);

        $this->assertExitSuccess();
    }

    /**
     * Set up the test case.
     */
    #[Before(-2)]
    protected function setCommandNamespaces(): void
    {
        $this->runner->clear();
        $this->runner->addNamespace('Tests\Mock\Commands');
    }

    #[Override]
    public static function setUpBeforeClass(): void
    {
        $loader = new Loader();
        $loader->addNamespaces([
            'Tests' => 'tests',
        ]);
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
