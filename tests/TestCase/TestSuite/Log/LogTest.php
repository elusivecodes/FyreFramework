<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Log;

use Fyre\Core\ErrorHandler;
use Fyre\Core\Loader;
use Fyre\TestSuite\TestCase;
use Fyre\TestSuite\Traits\LogTestTrait;
use Override;
use Tests\Mock\Application;

final class LogTest extends TestCase
{
    use LogIsEmptyTrait;
    use LogMessageContainsTrait;
    use LogMessageTrait;
    use LogTestTrait;

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

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->setupLogs([
            'test' => [
                'levels' => [
                    'emergency',
                    'alert',
                    'critical',
                    'error',
                ],
            ],
            'scoped' => [
                'levels' => [
                    'emergency',
                    'alert',
                    'critical',
                    'error',
                ],
                'scopes' => ['test'],
            ],
        ]);
    }
}
