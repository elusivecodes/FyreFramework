<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Integration;

use Fyre\Core\ErrorHandler;
use Fyre\Core\Loader;
use Fyre\TestSuite\TestCase;
use Fyre\TestSuite\Traits\IntegrationTestTrait;
use Override;
use Tests\Mock\Application;

final class IntegrationTest extends TestCase
{
    use ContentTypeTrait;
    use CookieSetTrait;
    use CookieTrait;
    use CsrfTrait;
    use FileTrait;
    use FlashMessageEqualsTrait;
    use HeaderContainsTrait;
    use HeaderTrait;
    use IntegrationTestTrait;
    use RedirectContainsTrait;
    use RedirectEqualsTrait;
    use RedirectTrait;
    use RequestDataTrait;
    use ResponseCodeTrait;
    use ResponseContainsTrait;
    use ResponseEmptyTrait;
    use ResponseEqualsTrait;
    use SessionHasKeyTrait;
    use SessionTrait;
    use UploadedFileTrait;

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
