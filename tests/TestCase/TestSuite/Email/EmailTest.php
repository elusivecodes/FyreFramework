<?php
declare(strict_types=1);

namespace Tests\TestCase\TestSuite\Email;

use Fyre\Core\ErrorHandler;
use Fyre\Core\Loader;
use Fyre\TestSuite\TestCase;
use Fyre\TestSuite\Traits\EmailTestTrait;
use Override;
use Tests\Mock\Application;

final class EmailTest extends TestCase
{
    use EmailTestTrait;
    use MailBodyContainsTrait;
    use MailContainsAttachmentTrait;
    use MailCountTrait;
    use MailSentFromTrait;
    use MailSentToTrait;
    use MailSentWithTrait;
    use MailSubjectContainsTrait;
    use NoMailSentTrait;

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
